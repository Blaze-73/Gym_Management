<?php

namespace App\Support;

class PlanCatalog
{
    /** Plan id 1 = S-TIER, 2 = INTERSTELLAR, 3 = ALPHA ORBIT */
    public static function definitions(): array
    {
        return [
            1 => [
                'name' => 'S-TIER PULSE',
                'price' => 49.00,
                'duration' => 30,
                'tag' => 'Essential',
                'period' => 'month',
                'popular' => false,
                'features' => [
                    '24/7 Gym Access',
                    'Standard Equipment',
                    'Locker Room Access',
                    'Weekly Progress Track',
                    'Workout Programs (beginner)',
                ],
                'entitlements' => self::baseEntitlements([
                    'nutrition_access' => false,
                    'schedule_access' => false,
                    'coaches_access' => false,
                    'store_discount_percent' => 0,
                ]),
            ],
            2 => [
                'name' => 'INTERSTELLAR',
                'price' => 399.00,
                'duration' => 365,
                'tag' => 'Most Popular',
                'period' => 'year',
                'popular' => true,
                'features' => [
                    'Everything in Alpha Orbit',
                    'Class Schedule & Priority Booking',
                    'Personal Coaches Access',
                    'VIP Priority Access',
                    'Sauna & Cryotherapy',
                    '12 Personal Training Sessions',
                    'Guest Passes (2/mo)',
                    'No store discount (full VIP bundle)',
                ],
                'entitlements' => self::baseEntitlements([
                    'nutrition_access' => true,
                    'nutrition_full' => true,
                    'schedule_access' => true,
                    'coaches_access' => true,
                    'priority_booking' => true,
                    'vip_access' => true,
                    'guest_passes_per_month' => 2,
                    'pt_sessions_total' => 12,
                    'recovery_zone' => true,
                    'workout_tiers' => ['beginner', 'intermediate', 'advanced'],
                    'store_discount_percent' => 0,
                ]),
            ],
            3 => [
                'name' => 'ALPHA ORBIT',
                'price' => 129.00,
                'duration' => 30,
                'tag' => 'Advanced',
                'period' => 'month',
                'popular' => false,
                'features' => [
                    'Everything in S-Tier Pulse',
                    'Full Nutrition Engine',
                    '30% Off Every Store Order',
                    'Premium Equipment',
                    'Recovery Zone Access',
                    '3 Guest Passes / month',
                    'Monthly Assessment',
                ],
                'entitlements' => self::baseEntitlements([
                    'nutrition_access' => true,
                    'nutrition_full' => true,
                    'schedule_access' => false,
                    'coaches_access' => false,
                    'recovery_zone' => true,
                    'guest_passes_per_month' => 3,
                    'monthly_assessment' => true,
                    'workout_tiers' => ['beginner', 'intermediate'],
                    'store_discount_percent' => 30,
                ]),
            ],
        ];
    }

    private static function baseEntitlements(array $overrides): array
    {
        return array_merge([
            'gym_access' => true,
            'nutrition_access' => false,
            'nutrition_full' => false,
            'schedule_access' => false,
            'coaches_access' => false,
            'priority_booking' => false,
            'vip_access' => false,
            'guest_passes_per_month' => 0,
            'pt_sessions_total' => 0,
            'recovery_zone' => false,
            'progress_tracking' => true,
            'monthly_assessment' => false,
            'workout_tiers' => ['beginner'],
            'store_discount_percent' => 0,
        ], $overrides);
    }

    public static function guestEntitlements(): array
    {
        return [
            'gym_access' => false,
            'nutrition_access' => false,
            'nutrition_full' => false,
            'schedule_access' => false,
            'coaches_access' => false,
            'priority_booking' => false,
            'vip_access' => false,
            'guest_passes_per_month' => 0,
            'pt_sessions_total' => 0,
            'recovery_zone' => false,
            'progress_tracking' => false,
            'monthly_assessment' => false,
            'workout_tiers' => [],
            'store_discount_percent' => 0,
        ];
    }

    public static function mergePlanRow(int $planId, ?array $dbRow = null): array
    {
        $dbRow = $dbRow ?? [];
        $base = self::definitions()[$planId] ?? null;

        if (!$base) {
            return [
                'id' => $planId,
                'name' => $dbRow['name'] ?? 'Plan',
                'price' => isset($dbRow['price']) ? (float) $dbRow['price'] : 0,
                'duration' => isset($dbRow['duration']) ? (int) $dbRow['duration'] : 30,
                'tag' => $dbRow['tag'] ?? '',
                'period' => $dbRow['period'] ?? 'month',
                'popular' => (bool) ($dbRow['popular'] ?? false),
                'features' => self::decodeList($dbRow['features'] ?? null),
                'entitlements' => self::decodeEntitlements($dbRow['entitlements'] ?? null),
            ];
        }

        $features = self::decodeList($dbRow['features'] ?? null);
        $entitlements = self::decodeEntitlements($dbRow['entitlements'] ?? null);

        return [
            'id' => $planId,
            'name' => $dbRow['name'] ?? $base['name'],
            'price' => isset($dbRow['price']) ? (float) $dbRow['price'] : $base['price'],
            'duration' => isset($dbRow['duration']) ? (int) $dbRow['duration'] : $base['duration'],
            'tag' => $dbRow['tag'] ?? $base['tag'],
            'period' => $dbRow['period'] ?? $base['period'],
            'popular' => isset($dbRow['popular']) ? (bool) $dbRow['popular'] : $base['popular'],
            'features' => !empty($features) ? $features : $base['features'],
            'entitlements' => $base['entitlements'],
        ];
    }

    public static function membershipPlanIds(): array
    {
        return [1, 2, 3];
    }

    /** Tier order: S-Tier (1) < Alpha Orbit (3) < Interstellar (2). */
    public static function tierRank(int $planId): int
    {
        return match ($planId) {
            1 => 1,
            3 => 2,
            2 => 3,
            default => 0,
        };
    }

    public static function canUpgrade(int $fromPlanId, int $toPlanId): bool
    {
        if ($fromPlanId === $toPlanId) {
            return false;
        }

        return self::tierRank($toPlanId) > self::tierRank($fromPlanId);
    }

    public static function canDowngrade(int $fromPlanId, int $toPlanId): bool
    {
        if ($fromPlanId === $toPlanId) {
            return false;
        }

        return self::tierRank($toPlanId) < self::tierRank($fromPlanId);
    }

    public static function canChangePlan(int $fromPlanId, int $toPlanId): bool
    {
        return self::canUpgrade($fromPlanId, $toPlanId) || self::canDowngrade($fromPlanId, $toPlanId);
    }

    /** @return int[] */
    public static function upgradePlanIds(?int $currentPlanId): array
    {
        if (!$currentPlanId) {
            return self::membershipPlanIds();
        }

        $currentRank = self::tierRank($currentPlanId);

        return array_values(array_filter(
            self::membershipPlanIds(),
            fn (int $id) => self::tierRank($id) > $currentRank
        ));
    }

    /** @return int[] */
    public static function downgradePlanIds(?int $currentPlanId): array
    {
        if (!$currentPlanId) {
            return [];
        }

        $currentRank = self::tierRank($currentPlanId);

        return array_values(array_filter(
            self::membershipPlanIds(),
            fn (int $id) => self::tierRank($id) < $currentRank
        ));
    }

    public static function downgradeWindowDays(): int
    {
        return 5;
    }

    public static function isDowngradeUnlocked(?int $daysRemaining): bool
    {
        return $daysRemaining !== null
            && $daysRemaining >= 0
            && $daysRemaining <= self::downgradeWindowDays();
    }

    public static function decodeList(mixed $value): array
    {
        if (is_array($value)) {
            return array_values($value);
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? array_values($decoded) : [];
        }
        return [];
    }

    public static function decodeEntitlements(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }
}
