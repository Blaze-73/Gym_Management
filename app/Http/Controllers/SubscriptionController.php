<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\PlanEntitlementService;
use App\Services\SubscriptionLifecycleService;
use App\Services\UserNotificationService;
use App\Support\PlanCatalog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    private const EXPIRING_SOON_DAYS = 7;

    public function __construct(
        protected SubscriptionLifecycleService $lifecycle,
        protected UserNotificationService $notifications
    ) {
    }

    public function me(Request $request, PlanEntitlementService $entitlements)
    {
        $subscription = $this->activeSubscriptionQuery($request->user()->id)->first();

        if (!$subscription) {
            return response()->json(['message' => 'No active subscription found'], 404);
        }

        $subscription->load(['plan', 'payment']);
        $subscription->days_remaining = $this->daysRemaining($subscription->end_date);
        $planDef = $entitlements->resolvePlanDefinition($subscription->plan);
        $planName = $planDef['name'] ?? $subscription->plan?->name ?? 'Membership Plan';
        $catalogId = $entitlements->catalogPlanId($subscription->plan);

        $data = $subscription->toArray();
        $data['days_remaining'] = $subscription->days_remaining;
        $data['is_subscribed'] = true;
        $data['status_label'] = 'Subscribed';
        $data['subscription_message'] = "Subscribed to {$planName}";
        $data['plan_id'] = $catalogId ?? $subscription->plan_id;
        $data['upgrade_plan_ids'] = PlanCatalog::upgradePlanIds($catalogId);
        $data['can_upgrade'] = count($data['upgrade_plan_ids']) > 0;
        $data['downgrade_plan_ids'] = PlanCatalog::downgradePlanIds($catalogId);
        $data['can_downgrade'] = count($data['downgrade_plan_ids']) > 0;
        $data['downgrade_window_days'] = PlanCatalog::downgradeWindowDays();
        $data['downgrade_unlocked'] = PlanCatalog::isDowngradeUnlocked($subscription->days_remaining);
        $data['can_downgrade_now'] = $data['can_downgrade'] && $data['downgrade_unlocked'];
        $data['can_change_plan'] = $data['can_upgrade'] || $data['can_downgrade'];
        $data['features'] = $planDef['features'] ?? [];
        $data['entitlements'] = $planDef['entitlements'] ?? [];
        if (isset($data['plan']) && is_array($data['plan'])) {
            $data['plan']['features'] = $data['features'];
            $data['plan']['entitlements'] = $data['entitlements'];
            $data['plan']['tag'] = $planDef['tag'] ?? null;
            $data['plan']['period'] = $planDef['period'] ?? 'month';
        }

        return response()->json($data);
    }

    public function alerts(Request $request)
    {
        $subscription = $this->activeSubscriptionQuery($request->user()->id)->first();

        if (!$subscription) {
            return response()->json(['alerts' => []]);
        }

        $subscription->load('plan');
        $daysLeft = $this->daysRemaining($subscription->end_date);
        $alerts = [];

        if ($daysLeft !== null && $daysLeft <= self::EXPIRING_SOON_DAYS && $daysLeft >= 0) {
            $alerts[] = [
                'id' => 'sub_expiring_' . $subscription->id,
                'type' => 'alert',
                'title' => 'Subscription Ending Soon',
                'body' => $daysLeft === 0
                    ? "Your {$subscription->plan->name} plan expires today. Renew to keep access."
                    : "Your {$subscription->plan->name} plan expires in {$daysLeft} day(s). Renew before " . Carbon::parse($subscription->end_date)->format('M j, Y') . '.',
                'read' => false,
                'time' => 'Now',
                'link' => '/plans',
            ];
        }

        return response()->json(['alerts' => $alerts]);
    }

    public function history(Request $request)
    {
        $subscriptions = Subscription::with(['plan', 'payment'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($subscriptions);
    }

    public function cancel(Request $request)
    {
        $subscription = $this->activeSubscriptionQuery($request->user()->id)->first();

        if (!$subscription) {
            return response()->json(['message' => 'No active subscription to cancel.'], 404);
        }

        $this->lifecycle->terminate($subscription, true);

        $subscription->loadMissing('plan');
        $planName = $subscription->plan?->name ?? 'your plan';
        $this->notifications->notifySubscriptionTerminated((int) $subscription->user_id, $planName);

        return response()->json([
            'message' => 'Your subscription has been terminated. Your workout and nutrition history has been saved. Please sign in again when you are ready to rejoin.',
            'force_logout' => true,
            'data_preserved' => true,
        ]);
    }

    public function adminIndex(Request $request)
    {
        $query = Subscription::with(['user', 'plan', 'payment'])
            ->orderByDesc('created_at');

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        return response()->json($query->paginate(min((int) $request->get('per_page', 50), 100)));
    }

    public function adminTerminate(Request $request, Subscription $subscription)
    {
        if ($subscription->payment_status === 'cancelled') {
            return response()->json(['message' => 'Subscription is already terminated.'], 422);
        }

        if ($subscription->payment_status !== 'paid') {
            return response()->json(['message' => 'Only paid subscriptions can be terminated.'], 422);
        }

        $this->lifecycle->terminate($subscription, true);

        $subscription->loadMissing(['plan', 'user']);
        $planName = $subscription->plan?->name ?? 'your plan';
        if ($subscription->user_id) {
            $this->notifications->notifySubscriptionTerminated((int) $subscription->user_id, $planName);
        }

        return response()->json([
            'message' => 'Subscription terminated. Member has been logged out; their account data is preserved.',
            'force_logout' => true,
            'data_preserved' => true,
            'subscription' => $subscription->fresh()->load(['user', 'plan', 'payment']),
        ]);
    }

    protected function activeSubscriptionQuery(int $userId)
    {
        return Subscription::with(['plan', 'payment'])
            ->where('user_id', $userId)
            ->where('payment_status', 'paid')
            ->where('end_date', '>=', now()->toDateString());
    }

    protected function daysRemaining($endDate): ?int
    {
        if (!$endDate) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays(Carbon::parse($endDate)->startOfDay(), false);
    }
}
