<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\UserNotificationService;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function __construct(protected UserNotificationService $notifications) {}

    public function storeOrders(Request $request)
    {
        $orders = Order::with(['user', 'items.product', 'payment'])
            ->orderByDesc('created_at')
            ->paginate(25);

        return response()->json($orders);
    }

    public function updateStoreOrderStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,shipped,delivered,processing,completed,cancelled',
        ]);

        $newStatus = $this->normalizeOrderStatus($validated['status']);
        $oldStatus = $order->status;

        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }
        }

        $order->update(['status' => $newStatus]);

        if ($order->user_id && $oldStatus !== $newStatus) {
            $this->notifications->notifyOrderStatus((int) $order->user_id, $order, $newStatus);
        }

        return response()->json([
            'message' => 'Order status updated.',
            'order' => $order->fresh()->load(['user', 'items.product', 'payment']),
        ]);
    }

    public function planPayments(Request $request)
    {
        $subscriptions = Subscription::with(['user', 'plan'])
            ->whereIn('payment_status', ['paid', 'pending', 'failed', 'cancelled'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($subscription) {
                $payment = Payment::where('subscription_id', $subscription->id)
                    ->orderByDesc('id')
                    ->first();

                return [
                    'subscription' => $subscription,
                    'payment' => $payment,
                    'user_name' => $subscription->customer_name ?? $subscription->user?->name,
                    'plan_name' => $subscription->plan?->name,
                    'amount' => $payment?->amount,
                    'payment_status' => $subscription->payment_status,
                    'transaction_id' => $payment?->transaction_id,
                    'start_date' => $subscription->start_date,
                    'end_date' => $subscription->end_date,
                ];
            });

        return response()->json($subscriptions);
    }

    protected function normalizeOrderStatus(string $status): string
    {
        return match ($status) {
            'processing' => 'shipped',
            'completed' => 'delivered',
            default => $status,
        };
    }
}
