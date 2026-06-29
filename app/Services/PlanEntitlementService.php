<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Support\PlanCatalog;
use Carbon\Carbon;

class PlanEntitlementService
{
    public function activeSubscriptionForUser(int $userId): ?Subscription
    {
        return Subscription::with('plan')
            ->where('user_id', $userId)
            ->where('payment_status', 'paid')
            ->where('end_date', '>=', Carbon::today())
            ->orderByDesc('end_date')
            ->first();
    }

    public function activeMembershipForUser(int $userId): ?Membership
    {
        return Membership::with('plan')
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->where('end_date', '>=', Carbon::today())
            ->orderByDesc('end_date')
            ->first();
    }

    /** Active paid subscription, or fall back to active membership. */
    public function activePlanForUser(?User $user): ?Plan
    {
        if (!$user) {
            return null;
        }

        $subscription = $this->activeSubscriptionForUser($user->id);
        if ($subscription?->plan) {
            return $subscription->plan;
        }

        return $this->activeMembershipForUser($user->id)?->plan;
    }

    /** Map DB plan row to catalog id (handles name mismatches). */
    public function catalogPlanId(?Plan $plan): ?int
    {
        if (!$plan) {
            return null;
        }

        $id = (int) $plan->id;
        if (isset(PlanCatalog::definitions()[$id])) {
            return $id;
        }

        $name = strtolower($plan->name ?? '');
        if (str_contains($name, 'alpha orbit')) {
            return 3;
        }
        if (str_contains($name, 'interstellar')) {
            return 2;
        }
        if (str_contains($name, 's-tier') || str_contains($name, 'pulse')) {
            return 1;
        }

        return $id;
    }

    public function resolvePlanDefinition(?Plan $plan): array
    {
        if (!$plan) {
            return [];
        }

        return PlanCatalog::mergePlanRow($this->catalogPlanId($plan), $plan->toArray());
    }

    private function normalizeEntitlements(array $entitlements, ?int $catalogPlanId): array
    {
        if ($catalogPlanId === 3) {
            $entitlements['schedule_access'] = false;
            $entitlements['nutrition_access'] = true;
            $entitlements['nutrition_full'] = true;
            $entitlements['store_discount_percent'] = max((int) ($entitlements['store_discount_percent'] ?? 0), 30);
            $entitlements['coaches_access'] = false;
        }
        if ($catalogPlanId === 2) {
            $entitlements['schedule_access'] = true;
            $entitlements['nutrition_access'] = true;
            $entitlements['coaches_access'] = true;
            $entitlements['nutrition_full'] = true;
            $entitlements['vip_access'] = true;
            $entitlements['priority_booking'] = true;
        }
        if ($catalogPlanId === 1) {
            $entitlements['schedule_access'] = false;
            $entitlements['coaches_access'] = false;
            $entitlements['nutrition_access'] = false;
        }

        return $entitlements;
    }

    public function entitlementsForUser(?User $user): array
    {
        if ($user && in_array($user->role, ['admin', 'coach'], true)) {
            return array_merge(PlanCatalog::guestEntitlements(), [
                'gym_access' => true,
                'nutrition_access' => true,
                'nutrition_full' => true,
                'schedule_access' => true,
                'coaches_access' => true,
                'priority_booking' => true,
                'vip_access' => true,
                'workout_tiers' => ['beginner', 'intermediate', 'advanced'],
                'store_discount_percent' => 0,
            ]);
        }

        $plan = $this->activePlanForUser($user);
        if (!$plan) {
            return PlanCatalog::guestEntitlements();
        }

        $def = $this->resolvePlanDefinition($plan);
        $ent = $def['entitlements'] ?? PlanCatalog::guestEntitlements();

        return $this->normalizeEntitlements($ent, $this->catalogPlanId($plan));
    }

    public function bundleForUser(?User $user): array
    {
        if ($user && in_array($user->role, ['admin', 'coach'], true)) {
            $passLabel = $user->role === 'coach' ? 'Coach Pass' : 'Staff Access';

            return [
                'subscribed' => true,
                'plan_id' => null,
                'plan_name' => $passLabel,
                'plan_tag' => $passLabel,
                'features' => ['Full platform access'],
                'entitlements' => $this->entitlementsForUser($user),
                'is_staff_pass' => true,
            ];
        }

        $subscription = $user ? $this->activeSubscriptionForUser($user->id) : null;
        $membership = $user ? $this->activeMembershipForUser($user->id) : null;
        $plan = $subscription?->plan ?? $membership?->plan;

        if (!$plan) {
            return [
                'subscribed' => false,
                'plan_id' => null,
                'plan_name' => null,
                'plan_tag' => null,
                'features' => [],
                'entitlements' => PlanCatalog::guestEntitlements(),
            ];
        }

        $def = $this->resolvePlanDefinition($plan);
        $catalogId = $this->catalogPlanId($plan);
        $endDate = $subscription?->end_date ?? $membership?->end_date;
        $daysRemaining = $endDate
            ? (int) now()->startOfDay()->diffInDays(Carbon::parse($endDate)->startOfDay(), false)
            : null;
        $ent = $this->normalizeEntitlements(
            $def['entitlements'] ?? PlanCatalog::guestEntitlements(),
            $catalogId
        );

        return [
            'subscribed' => true,
            'plan_id' => $catalogId ?? $plan->id,
            'plan_name' => $def['name'] ?? $plan->name,
            'plan_tag' => $def['tag'] ?? null,
            'features' => $def['features'] ?? [],
            'entitlements' => $ent,
            'subscription_end_date' => $endDate?->format('Y-m-d'),
            'upgrade_plan_ids' => PlanCatalog::upgradePlanIds($catalogId),
            'can_upgrade' => count(PlanCatalog::upgradePlanIds($catalogId)) > 0,
            'downgrade_plan_ids' => PlanCatalog::downgradePlanIds($catalogId),
            'can_downgrade' => count(PlanCatalog::downgradePlanIds($catalogId)) > 0,
            'downgrade_window_days' => PlanCatalog::downgradeWindowDays(),
            'downgrade_unlocked' => PlanCatalog::isDowngradeUnlocked($daysRemaining),
            'can_downgrade_now' => count(PlanCatalog::downgradePlanIds($catalogId)) > 0
                && PlanCatalog::isDowngradeUnlocked($daysRemaining),
            'days_remaining' => $daysRemaining,
            'can_change_plan' => count(PlanCatalog::upgradePlanIds($catalogId)) > 0
                || count(PlanCatalog::downgradePlanIds($catalogId)) > 0,
        ];
    }

    public function hasEntitlement(?User $user, string $key): bool
    {
        $ent = $this->entitlementsForUser($user);

        if ($key === 'nutrition_access') {
            return !empty($ent['nutrition_access']);
        }

        return !empty($ent[$key]);
    }

    public function storeDiscountPercent(?User $user): int
    {
        $ent = $this->entitlementsForUser($user);
        $pct = (int) ($ent['store_discount_percent'] ?? 0);

        return max(0, min(90, $pct));
    }

    public function applyStoreDiscount(float $subtotal, ?User $user): array
    {
        $percent = $this->storeDiscountPercent($user);
        $discount = $percent > 0 ? round($subtotal * $percent / 100, 2) : 0.0;
        $total = round(max(0, $subtotal - $discount), 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount_percent' => $percent,
            'discount_amount' => $discount,
            'total' => $total,
        ];
    }

    public function deniedResponse(string $feature, string $message): array
    {
        return [
            'message' => $message,
            'upgrade' => true,
            'required_entitlement' => $feature,
        ];
    }

    public function formatPlanForApi(Plan $plan): array
    {
        $def = PlanCatalog::mergePlanRow((int) $plan->id, $plan->toArray());

        return [
            'id' => $plan->id,
            'name' => $def['name'] ?? $plan->name,
            'price' => $def['price'] ?? (float) $plan->price,
            'duration' => $def['duration'] ?? (int) $plan->duration,
            'tag' => $def['tag'] ?? '',
            'period' => $def['period'] ?? 'month',
            'popular' => (bool) ($def['popular'] ?? false),
            'features' => $def['features'] ?? [],
            'entitlements' => $def['entitlements'] ?? [],
            'created_at' => $plan->created_at,
            'updated_at' => $plan->updated_at,
        ];
    }
}
