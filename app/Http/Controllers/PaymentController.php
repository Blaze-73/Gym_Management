<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesCheckoutCustomer;
use App\Models\Membership;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Support\PlanCatalog;
use App\Services\PayPalService;
use App\Services\PlanEntitlementService;
use App\Services\UserNotificationService;
use App\Services\SubscriptionLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentController extends Controller
{
    use ValidatesCheckoutCustomer;

    private const ALLOWED_PLAN_IDS = [1, 2, 3];

    public function __construct(
        protected PayPalService $paypal,
        protected PlanEntitlementService $entitlements,
        protected UserNotificationService $notifications,
        protected SubscriptionLifecycleService $lifecycle
    ) {
    }

    public function status()
    {
        return response()->json([
            'paypal_configured' => $this->paypal->hasCredentials() && !$this->paypal->isMockMode(),
            'mock_mode' => $this->paypal->isMockMode(),
            'mode' => $this->paypal->mode(),
        ]);
    }

    public function checkoutPlan(Request $request)
    {
        if ($err = $this->paypalConfigError()) {
            return $err;
        }

        $validated = $request->validate(array_merge(
            ['plan_id' => 'required|integer|in:1,2,3'],
            $this->checkoutCustomerRules()
        ));

        $customer = $this->normalizeCustomerInfo($validated);
        $user = $request->user();
        $planId = (int) $validated['plan_id'];

        $activeSub = Subscription::where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->where('end_date', '>=', now()->toDateString())
            ->orderByDesc('end_date')
            ->first();

        if ($activeSub) {
            if ((int) $activeSub->plan_id === $planId) {
                return response()->json([
                    'message' => 'You are already subscribed to this plan.',
                ], 422);
            }
            if (!PlanCatalog::canChangePlan((int) $activeSub->plan_id, $planId)) {
                return response()->json([
                    'message' => 'Choose a different plan tier to upgrade or downgrade.',
                ], 422);
            }
        } elseif ($this->hasActiveSubscription($user->id)) {
            return response()->json([
                'message' => 'You already have an active subscription.',
            ], 422);
        }

        $plan = Plan::find($planId);
        if (!$plan) {
            return response()->json(['message' => 'Plan not found.'], 404);
        }

        $start = now()->startOfDay();
        $end = $start->copy()->addDays($plan->duration);

        $isUpgrade = $activeSub && PlanCatalog::canUpgrade((int) $activeSub->plan_id, $planId);
        $isDowngrade = $activeSub && PlanCatalog::canDowngrade((int) $activeSub->plan_id, $planId);

        if ($isDowngrade && $activeSub) {
            $daysLeft = (int) now()->startOfDay()->diffInDays(
                \Carbon\Carbon::parse($activeSub->end_date)->startOfDay(),
                false
            );
            if (!PlanCatalog::isDowngradeUnlocked($daysLeft)) {
                return response()->json([
                    'message' => 'Downgrade unlocks within ' . PlanCatalog::downgradeWindowDays()
                        . ' days of your current plan end date.',
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'start_date' => $start,
                'end_date' => $end,
                'payment_status' => 'pending',
                'customer_name' => $customer['customer_name'],
                'customer_email' => $customer['customer_email'],
                'customer_phone' => $customer['customer_phone'],
                'billing_address' => $customer['customer_address'],
            ]);

            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => $plan->price,
                'type' => 'plan',
                'status' => 'pending',
                'subscription_id' => $subscription->id,
                'metadata' => array_merge(
                    [
                        'plan_id' => $plan->id,
                        'plan_name' => $plan->name,
                        'is_upgrade' => $isUpgrade,
                        'is_downgrade' => $isDowngrade,
                        'previous_plan_id' => $activeSub?->plan_id,
                    ],
                    $customer
                ),
            ]);

            $frontendUrl = rtrim(config('services.paypal.frontend_url', 'http://localhost:5173'), '/');
            $paypalOrder = $this->paypal->createCheckoutOrder(
                (float) $plan->price,
                "Gym Plan: {$plan->name}",
                "{$frontendUrl}/payment/success?payment_id={$payment->id}",
                "{$frontendUrl}/payment/cancel?payment_id={$payment->id}",
                "plan-{$payment->id}"
            );

            $payment->update(['paypal_order_id' => $paypalOrder['paypal_order_id']]);

            DB::commit();

            return response()->json([
                'message' => $isUpgrade
                    ? 'Redirect to PayPal to complete your plan upgrade.'
                    : ($isDowngrade
                        ? 'Redirect to PayPal to complete your plan downgrade.'
                        : 'Redirect to PayPal Sandbox to complete payment.'),
                'payment_id' => $payment->id,
                'approval_url' => $paypalOrder['approval_url'],
                'is_upgrade' => $isUpgrade,
                'is_downgrade' => $isDowngrade,
            ]);
        } catch (RuntimeException $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 502);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Payment initialization failed.'], 500);
        }
    }

    public function checkoutStore(Request $request)
    {
        if ($err = $this->paypalConfigError()) {
            return $err;
        }

        $validated = $request->validate(array_merge(
            [
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1|max:99',
                'notes' => 'nullable|string|max:500',
            ],
            $this->checkoutCustomerRules()
        ));

        $customer = $this->normalizeCustomerInfo($validated);
        $user = $request->user();
        $orderItems = [];
        $totalAmount = 0;

        foreach ($validated['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);

            if (!$product->isActive()) {
                return response()->json([
                    'message' => "Product '{$product->name}' is not available.",
                ], 422);
            }

            if (!$product->isInStock($item['quantity'])) {
                return response()->json([
                    'message' => "Insufficient stock for '{$product->name}'.",
                ], 422);
            }

            $subtotal = $product->price * $item['quantity'];
            $totalAmount += $subtotal;

            $orderItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $item['quantity'],
                'price' => $product->price,
                'subtotal' => $subtotal,
            ];
        }

        if ($totalAmount <= 0) {
            return response()->json(['message' => 'Invalid cart total.'], 422);
        }

        $pricing = $this->entitlements->applyStoreDiscount($totalAmount, $user);
        $chargeAmount = $pricing['total'];

        try {
            DB::beginTransaction();

            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => $chargeAmount,
                'type' => 'store',
                'status' => 'pending',
                'metadata' => array_merge(
                    [
                        'items' => $orderItems,
                        'notes' => $validated['notes'] ?? null,
                        'subtotal' => $pricing['subtotal'],
                        'discount_percent' => $pricing['discount_percent'],
                        'discount_amount' => $pricing['discount_amount'],
                    ],
                    $customer
                ),
            ]);

            $frontendUrl = rtrim(config('services.paypal.frontend_url', 'http://localhost:5173'), '/');
            $paypalOrder = $this->paypal->createCheckoutOrder(
                (float) $chargeAmount,
                'Gym Store Order',
                "{$frontendUrl}/payment/success?payment_id={$payment->id}",
                "{$frontendUrl}/payment/cancel?payment_id={$payment->id}",
                "store-{$payment->id}"
            );

            $payment->update(['paypal_order_id' => $paypalOrder['paypal_order_id']]);

            DB::commit();

            return response()->json([
                'message' => 'Redirect to PayPal Sandbox to complete payment.',
                'payment_id' => $payment->id,
                'approval_url' => $paypalOrder['approval_url'],
                'pricing' => $pricing,
            ]);
        } catch (RuntimeException $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 502);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Payment initialization failed.'], 500);
        }
    }

    public function capture(Request $request)
    {
        $validated = $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'token' => 'required|string',
        ]);

        $user = $request->user();
        $payment = Payment::findOrFail($validated['payment_id']);

        if ($payment->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($payment->status === 'paid') {
            return response()->json([
                'message' => 'Payment already completed.',
                'payment' => $payment->load(['subscription.plan', 'order.items.product']),
            ]);
        }

        if ($payment->paypal_order_id !== $validated['token']) {
            return response()->json(['message' => 'Invalid PayPal token for this payment.'], 422);
        }

        try {
            $captureData = $this->paypal->captureOrder($payment->paypal_order_id);
        } catch (RuntimeException $e) {
            $payment->update(['status' => 'failed']);
            if ($payment->subscription) {
                $payment->subscription->update(['payment_status' => 'failed']);
            }
            return response()->json(['message' => $e->getMessage()], 502);
        }

        if (($captureData['status'] ?? '') !== 'COMPLETED') {
            $payment->update(['status' => 'failed']);
            return response()->json(['message' => 'PayPal payment was not completed.'], 422);
        }

        $capture = $captureData['purchase_units'][0]['payments']['captures'][0] ?? [];
        $transactionId = $capture['id'] ?? null;

        if (!$transactionId) {
            return response()->json(['message' => 'PayPal transaction ID missing.'], 422);
        }

        if (Payment::where('transaction_id', $transactionId)->where('id', '!=', $payment->id)->exists()) {
            return response()->json(['message' => 'This transaction has already been used.'], 422);
        }

        try {
            DB::beginTransaction();

            $payment->update([
                'status' => 'paid',
                'transaction_id' => $transactionId,
            ]);

            if ($payment->type === 'plan') {
                $this->activatePlanPayment($payment);
            } else {
                $this->fulfillStorePayment($payment);
            }

            DB::commit();

            return response()->json([
                'message' => 'Payment successful.',
                'payment' => $payment->fresh()->load(['subscription.plan', 'order.items.product']),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Payment captured but fulfillment failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function cancel(Request $request)
    {
        $validated = $request->validate([
            'payment_id' => 'required|exists:payments,id',
        ]);

        $payment = Payment::findOrFail($validated['payment_id']);

        if ($payment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($payment->status === 'pending') {
            $payment->update(['status' => 'failed']);
            if ($payment->subscription && $payment->subscription->payment_status === 'pending') {
                $payment->subscription->update(['payment_status' => 'cancelled']);
            }
        }

        return response()->json(['message' => 'Payment cancelled.']);
    }

    protected function activatePlanPayment(Payment $payment): void
    {
        $subscription = $payment->subscription;
        if (!$subscription) {
            throw new RuntimeException('Subscription record missing.');
        }

        if ($subscription->payment_status === 'paid') {
            return;
        }

        $subscription->update(['payment_status' => 'paid']);

        $this->lifecycle->supersedePreviousPlans($payment->user_id, $subscription->id);

        Membership::create([
            'user_id' => $payment->user_id,
            'plan_id' => $subscription->plan_id,
            'start_date' => $subscription->start_date,
            'end_date' => $subscription->end_date,
            'status' => 'active',
        ]);

        $this->notifications->notifySubscriptionActivated($payment->user_id, $subscription->fresh());
    }

    protected function fulfillStorePayment(Payment $payment): void
    {
        $metadata = $payment->metadata ?? [];
        $items = $metadata['items'] ?? [];

        if (empty($items)) {
            throw new RuntimeException('Order items missing from payment metadata.');
        }

        $order = Order::create([
            'user_id' => $payment->user_id,
            'total_amount' => $payment->amount,
            'status' => 'pending',
            'payment_status' => 'paid',
            'customer_name' => $metadata['customer_name'] ?? null,
            'customer_email' => $metadata['customer_email'] ?? null,
            'customer_phone' => $metadata['customer_phone'] ?? null,
            'shipping_address' => $metadata['customer_address'] ?? null,
            'notes' => $metadata['notes'] ?? null,
        ]);

        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);

            if (!$product->isInStock($item['quantity'])) {
                throw new RuntimeException("Insufficient stock for '{$product->name}'.");
            }

            $product->decrement('stock', $item['quantity']);

            $order->items()->create([
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'subtotal' => $item['subtotal'],
            ]);
        }

        $payment->update(['order_id' => $order->id]);

        $this->notifications->notifyOrderPlaced((int) $payment->user_id, $order);
    }

    protected function hasActiveSubscription(int $userId, ?int $excludeId = null): bool
    {
        $query = Subscription::where('user_id', $userId)
            ->where('payment_status', 'paid')
            ->where('end_date', '>=', now()->toDateString());

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    protected function paypalConfigError()
    {
        if ($this->paypal->isMockMode()) {
            return response()->json([
                'message' => 'PayPal mock mode is enabled. Set PAYPAL_MOCK=false in .env to use real PayPal Sandbox.',
            ], 503);
        }

        if (!$this->paypal->hasCredentials()) {
            return response()->json([
                'message' => 'PayPal Sandbox credentials missing. Add PAYPAL_CLIENT_ID and PAYPAL_SECRET to .env from developer.paypal.com, then run: php artisan paypal:verify',
            ], 503);
        }

        return null;
    }
}
