<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Services\UserNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(protected UserNotificationService $notifications) {}

    /**
     * Display a listing of orders
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.product', 'payment']);

        // If client, show only their orders
        if ($request->user()->isClient()) {
            $query->where('user_id', $request->user()->id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user (admin only)
        if ($request->has('user_id') && $request->user()->isAdmin()) {
            $query->where('user_id', $request->user_id);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($orders);
    }

    /**
     * Store a newly created order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Calculate total and validate stock
            $totalAmount = 0;
            $orderItems = [];

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Check if product is active
                if (!$product->isActive()) {
                    throw new \Exception("Product '{$product->name}' is not available");
                }

                // Check stock
                if (!$product->isInStock($item['quantity'])) {
                    throw new \Exception("Insufficient stock for product '{$product->name}'. Available: {$product->stock}");
                }

                $subtotal = $product->price * $item['quantity'];
                $totalAmount += $subtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ];

                // Reduce stock
                $product->decrement('stock', $item['quantity']);
            }

            // Create order
            $order = Order::create([
                'user_id' => $request->user()->id,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create order items
            foreach ($orderItems as $item) {
                $order->items()->create($item);
            }

            DB::commit();

            $this->notifications->notifyOrderPlaced((int) $request->user()->id, $order);

            return response()->json([
                'message' => 'Order placed successfully',
                'order' => $order->load(['items.product', 'user'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Order failed: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified order
     */
    public function show(Request $request, Order $order)
    {
        // Clients can only view their own orders
        if ($request->user()->isClient() && $order->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized to view this order'
            ], 403);
        }

        $order->load(['items.product', 'user']);

        return response()->json([
            'order' => $order
        ]);
    }

    /**
     * Update order status (admin only)
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,shipped,delivered,processing,completed,cancelled',
        ]);

        $newStatus = match ($validated['status']) {
            'processing' => 'shipped',
            'completed' => 'delivered',
            default => $validated['status'],
        };

        $oldStatus = $order->status;
        $order->update(['status' => $newStatus]);

        // If order is cancelled, restore stock
        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            foreach ($order->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $order->load(['items.product', 'user'])
        ]);
    }

    /**
     * Cancel order (users can cancel their own pending orders)
     */
    public function cancel(Request $request, Order $order)
    {
        // Check ownership
        if ($request->user()->isClient() && $order->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized to cancel this order'
            ], 403);
        }

        // Can only cancel pending orders
        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Can only cancel pending orders'
            ], 422);
        }

        // Restore stock
        foreach ($order->items as $item) {
            $item->product->increment('stock', $item->quantity);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Order cancelled successfully',
            'order' => $order->load(['items.product'])
        ]);
    }

    /**
     * Get order statistics (admin only)
     */
    public function statistics()
    {
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'shipped_orders' => Order::whereIn('status', ['shipped', 'processing'])->count(),
            'delivered_orders' => Order::whereIn('status', ['delivered', 'completed'])->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'total_revenue' => Order::whereIn('status', ['delivered', 'completed'])->sum('total_amount'),
            'pending_revenue' => Order::whereIn('status', ['pending', 'shipped', 'processing'])->sum('total_amount'),
        ];

        return response()->json([
            'stats' => $stats
        ]);
    }
}