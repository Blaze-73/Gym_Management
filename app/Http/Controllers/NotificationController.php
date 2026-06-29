<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Models\Subscription;
use App\Models\UserNotification;
use App\Services\PlanEntitlementService;
use App\Services\UserNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private const EXPIRING_SOON_DAYS = 7;

    public function index(Request $request, UserNotificationService $notifications)
    {
        $userId = $request->user()->id;

        $activeSub = Subscription::with('plan')
            ->where('user_id', $userId)
            ->where('payment_status', 'paid')
            ->where('end_date', '>=', Carbon::today())
            ->orderByDesc('end_date')
            ->first();

        if ($activeSub) {
            $notifications->notifySubscriptionActivated($userId, $activeSub);

            $daysLeft = (int) now()->startOfDay()->diffInDays(Carbon::parse($activeSub->end_date)->startOfDay(), false);
            if ($daysLeft <= self::EXPIRING_SOON_DAYS && $daysLeft >= 0) {
                $notifications->notifySubscriptionExpiring($userId, $activeSub, $daysLeft);
            }
        } else {
            $membership = Membership::with('plan')
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->where('end_date', '>=', Carbon::today())
                ->orderByDesc('end_date')
                ->first();

            if ($membership?->plan) {
                $entitlements = app(PlanEntitlementService::class);
                $planDef = $entitlements->resolvePlanDefinition($membership->plan);
                $planName = $planDef['name'] ?? $membership->plan->name ?? 'your plan';
                $notifications->notifyPlanActivated($userId, $planName, 'membership_' . $membership->id);
            }
        }

        return response()->json([
            'notifications' => $notifications->listForUser($userId),
        ]);
    }

    public function markRead(Request $request, UserNotification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $notification->update(['read' => true]);

        return response()->json(['message' => 'Marked as read']);
    }

    public function markAllRead(Request $request)
    {
        UserNotification::where('user_id', $request->user()->id)
            ->where('read', false)
            ->update(['read' => true]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function destroy(Request $request, UserNotification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $notification->delete();

        return response()->json(['message' => 'Notification removed']);
    }
}
