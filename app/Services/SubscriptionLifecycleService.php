<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\Subscription;
use App\Models\User;

class SubscriptionLifecycleService
{
    /**
     * End a subscription immediately. Keeps user history (nutrition, workouts, orders, etc.).
     * Optionally revokes API tokens so the member must sign in again.
     */
    public function terminate(Subscription $subscription, bool $revokeTokens = true): User
    {
        $subscription->loadMissing('user');

        $subscription->update([
            'payment_status' => 'cancelled',
            'end_date' => now()->toDateString(),
        ]);

        Membership::where('user_id', $subscription->user_id)
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        $user = $subscription->user;
        if ($revokeTokens && $user) {
            $user->tokens()->delete();
        }

        return $user;
    }

    /** Terminate any active paid subscription for a user (e.g. legacy membership cancel). */
    public function terminateActiveForUser(int $userId, bool $revokeTokens = true): ?User
    {
        $subscription = Subscription::where('user_id', $userId)
            ->where('payment_status', 'paid')
            ->where('end_date', '>=', now()->toDateString())
            ->orderByDesc('end_date')
            ->first();

        if (!$subscription) {
            Membership::where('user_id', $userId)
                ->where('status', 'active')
                ->update(['status' => 'cancelled']);

            if ($revokeTokens) {
                User::find($userId)?->tokens()->delete();
            }

            return User::find($userId);
        }

        return $this->terminate($subscription, $revokeTokens);
    }

    /** Cancel other active paid subscriptions when a new plan payment succeeds. */
    public function supersedePreviousPlans(int $userId, int $keepSubscriptionId): void
    {
        Subscription::where('user_id', $userId)
            ->where('id', '!=', $keepSubscriptionId)
            ->where('payment_status', 'paid')
            ->where('end_date', '>=', now()->toDateString())
            ->update([
                'payment_status' => 'cancelled',
                'end_date' => now()->toDateString(),
            ]);

        Membership::where('user_id', $userId)
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);
    }
}
