<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Models\Order;
use App\Models\UserCoach;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function index(Request $request)
    {
        $items = [];

        Membership::with(['user', 'plan'])
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->each(function (Membership $m) use (&$items) {
                $items[] = [
                    'id' => 'membership_' . $m->id,
                    'type' => 'user',
                    'title' => 'Membership request',
                    'body' => ($m->user?->name ?? 'Member') . ' requested ' . ($m->plan?->name ?? 'a plan') . '.',
                    'read' => false,
                    'time' => $m->created_at?->diffForHumans() ?? 'Recent',
                    'link' => '/admin/members',
                    'membershipId' => $m->id,
                ];
            });

        UserCoach::with(['user', 'coach.user'])
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->each(function (UserCoach $a) use (&$items) {
                $coachName = $a->coach?->user?->name ?? $a->coach?->name ?? 'a coach';
                $items[] = [
                    'id' => 'coach_req_' . $a->id,
                    'type' => 'coach',
                    'title' => 'Coach join request',
                    'body' => ($a->user?->name ?? 'Member') . ' wants to join ' . $coachName . '.',
                    'read' => false,
                    'time' => $a->created_at?->diffForHumans() ?? 'Recent',
                    'link' => '/admin/coaches',
                ];
            });

        UserCoach::with(['user', 'coach.user'])
            ->where('status', 'leave_pending')
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get()
            ->each(function (UserCoach $a) use (&$items) {
                $coachName = $a->coach?->user?->name ?? $a->coach?->name ?? 'their coach';
                $items[] = [
                    'id' => 'leave_req_' . $a->id,
                    'type' => 'alert',
                    'title' => 'Leave coach request',
                    'body' => ($a->user?->name ?? 'Member') . ' wants to leave ' . $coachName . '.',
                    'read' => false,
                    'time' => $a->updated_at?->diffForHumans() ?? 'Recent',
                    'link' => '/admin/coaches',
                ];
            });

        Order::with('user')
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get()
            ->each(function (Order $order) use (&$items) {
                $customer = $order->user?->name ?? $order->customer_name ?? 'Customer';
                $items[] = [
                    'id' => 'order_' . $order->id,
                    'type' => 'order',
                    'title' => 'New store order',
                    'body' => "{$customer} placed order #{$order->id} ($" . number_format((float) $order->total_amount, 2) . ').',
                    'read' => false,
                    'time' => $order->created_at?->diffForHumans() ?? 'Recent',
                    'link' => '/admin/products',
                ];
            });

        return response()->json(['notifications' => $items]);
    }
}
