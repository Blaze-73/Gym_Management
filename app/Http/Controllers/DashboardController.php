<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Membership;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Schedule;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    private function formatDate($value, string $format = 'Y-m-d'): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format($format);
        } catch (\Exception) {
            return is_string($value) ? substr($value, 0, 10) : null;
        }
    }

    /**
     * Get admin dashboard statistics
     */
    public function index()
    {
        $stats = [
            // User Stats
            'total_users' => User::count(),
            'total_clients' => User::where('role', 'client')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'new_users_this_month' => User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),

            // Membership Stats
            'total_memberships' => Membership::count(),
            'active_memberships' => Membership::where('status', 'active')
                ->where('end_date', '>', now())
                ->count(),
            'expired_memberships' => Membership::where('end_date', '<=', now())
                ->count(),
            'expiring_soon' => Membership::where('status', 'active')
                ->whereBetween('end_date', [now(), now()->addDays(7)])
                ->count(),

            // Revenue Stats
            'total_revenue' => $this->calculateTotalRevenue(),
            'monthly_revenue' => $this->calculateMonthlyRevenue(),
            'collected_revenue_month' => $this->calculateCollectedRevenueMonth(),
            'store_revenue_month' => $this->calculateStoreRevenueMonth(),
            'plan_revenue_month' => $this->calculatePlanRevenueMonth(),

            // Plan Stats
            'total_plans' => Plan::count(),
            'most_popular_plan' => $this->getMostPopularPlan(),
        ];

        return response()->json([
            'stats' => $stats,
            'recent_memberships' => Membership::with(['user', 'plan'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'recent_activity' => $this->getRecentActivity(),
        ]);
    }

    /**
     * Unified feed: payments, orders, memberships, and live check-ins.
     */
    private function getRecentActivity(int $limit = 10): array
    {
        $events = [];

        if (Schema::hasTable('payments')) {
            Payment::with(['user', 'subscription.plan', 'order'])
                ->where('status', 'paid')
                ->orderByDesc('updated_at')
                ->limit($limit)
                ->get()
                ->each(function (Payment $payment) use (&$events) {
                    $isPlan = $payment->type === 'plan';
                    $events[] = [
                        'id' => 'payment-' . $payment->id,
                        'kind' => $isPlan ? 'plan_payment' : 'store_payment',
                        'name' => $payment->user?->name ?? 'Guest',
                        'detail' => $isPlan
                            ? ($payment->subscription?->plan?->name ?? 'Membership plan')
                            : ('Order ' . ($payment->order?->order_number ?? ('#' . $payment->order_id))),
                        'amount' => (float) $payment->amount,
                        'status' => 'paid',
                        'at' => ($payment->updated_at ?? $payment->created_at)?->toIso8601String(),
                    ];
                });
        }

        if (Schema::hasTable('orders')) {
            Order::with('user')
                ->orderByDesc('created_at')
                ->limit(6)
                ->get()
                ->each(function (Order $order) use (&$events) {
                    $events[] = [
                        'id' => 'order-' . $order->id,
                        'kind' => 'store_order',
                        'name' => $order->customer_name ?: ($order->user?->name ?? 'Guest'),
                        'detail' => $order->order_number ?? ('Order #' . $order->id),
                        'amount' => (float) $order->total_amount,
                        'status' => $order->payment_status ?? $order->status ?? 'pending',
                        'at' => $order->created_at?->toIso8601String(),
                    ];
                });
        }

        Membership::with(['user', 'plan'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->each(function (Membership $membership) use (&$events) {
                $events[] = [
                    'id' => 'membership-' . $membership->id,
                    'kind' => 'membership',
                    'name' => $membership->user?->name ?? 'Member',
                    'detail' => $membership->plan?->name ?? 'Membership',
                    'amount' => (float) ($membership->plan?->price ?? 0),
                    'status' => $membership->status,
                    'at' => $membership->created_at?->toIso8601String(),
                ];
            });

        Attendance::with('user')
            ->whereDate('check_in', Carbon::today())
            ->orderByDesc('check_in')
            ->limit(6)
            ->get()
            ->each(function (Attendance $attendance) use (&$events) {
                $events[] = [
                    'id' => 'checkin-' . $attendance->id,
                    'kind' => 'check_in',
                    'name' => $attendance->user?->name ?? 'Member',
                    'detail' => 'Checked in',
                    'amount' => null,
                    'status' => 'active',
                    'at' => $attendance->check_in
                        ? Carbon::parse($attendance->check_in)->toIso8601String()
                        : null,
                ];
            });

        usort($events, function ($a, $b) {
            return strcmp($b['at'] ?? '', $a['at'] ?? '');
        });

        return array_slice($events, 0, $limit);
    }

    private function calculateCollectedRevenueMonth(): float
    {
        if (!Schema::hasTable('payments')) {
            return 0.0;
        }

        return (float) Payment::where('status', 'paid')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->sum('amount');
    }

    private function calculateStoreRevenueMonth(): float
    {
        if (!Schema::hasTable('payments')) {
            return 0.0;
        }

        return (float) Payment::where('status', 'paid')
            ->where('type', 'store')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->sum('amount');
    }

    private function calculatePlanRevenueMonth(): float
    {
        if (!Schema::hasTable('payments')) {
            return 0.0;
        }

        return (float) Payment::where('status', 'paid')
            ->where('type', 'plan')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->sum('amount');
    }

    /**
     * Calculate total revenue from all active memberships
     */
    private function calculateTotalRevenue()
    {
        return Membership::join('plans', 'memberships.plan_id', '=', 'plans.id')
            ->where('memberships.status', 'active')
            ->sum('plans.price');
    }

    /**
     * Calculate revenue for current month
     */
    private function calculateMonthlyRevenue()
    {
        $collected = $this->calculateCollectedRevenueMonth();
        if ($collected > 0) {
            return $collected;
        }

        return Membership::join('plans', 'memberships.plan_id', '=', 'plans.id')
            ->whereMonth('memberships.created_at', now()->month)
            ->whereYear('memberships.created_at', now()->year)
            ->sum('plans.price');
    }

    /**
     * Get the most popular plan
     */
    private function getMostPopularPlan()
    {
        $plan = Membership::select('plan_id')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('plan_id')
            ->orderBy('total', 'desc')
            ->with('plan')
            ->first();

        return $plan ? [
            'plan' => $plan->plan,
            'total_subscriptions' => $plan->total
        ] : null;
    }

    /**
     * Get membership trends (last 6 months)
     */
    public function trends()
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $membershipRevenue = (float) Membership::join('plans', 'memberships.plan_id', '=', 'plans.id')
                ->whereMonth('memberships.created_at', $date->month)
                ->whereYear('memberships.created_at', $date->year)
                ->sum('plans.price');

            $collectedRevenue = 0.0;
            if (Schema::hasTable('payments')) {
                $collectedRevenue = (float) Payment::where('status', 'paid')
                    ->whereMonth('updated_at', $date->month)
                    ->whereYear('updated_at', $date->year)
                    ->sum('amount');
            }

            $months[] = [
                'month' => $date->format('M Y'),
                'new_memberships' => Membership::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
                'revenue' => $collectedRevenue > 0 ? $collectedRevenue : $membershipRevenue,
            ];
        }

        return response()->json([
            'trends' => $months
        ]);
    }

    /**
     * Full data bundle for admin PDF/HTML reports.
     */
    public function exportReport()
    {
        $weekStart = now()->startOfWeek(Carbon::MONDAY)->toDateString();

        $stats = [
            'total_users' => User::count(),
            'total_clients' => User::where('role', 'client')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'new_users_this_month' => User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'total_memberships' => Membership::count(),
            'active_memberships' => Membership::where('status', 'active')
                ->where('end_date', '>', now())
                ->count(),
            'expired_memberships' => Membership::where('end_date', '<=', now())->count(),
            'expiring_soon' => Membership::where('status', 'active')
                ->whereBetween('end_date', [now(), now()->addDays(7)])
                ->count(),
            'total_revenue' => (float) $this->calculateTotalRevenue(),
            'monthly_revenue' => (float) $this->calculateMonthlyRevenue(),
            'total_plans' => Plan::count(),
            'most_popular_plan' => $this->getMostPopularPlan(),
        ];

        $trends = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $trends[] = [
                'month' => $date->format('M Y'),
                'new_memberships' => Membership::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
                'revenue' => (float) Membership::join('plans', 'memberships.plan_id', '=', 'plans.id')
                    ->whereMonth('memberships.created_at', $date->month)
                    ->whereYear('memberships.created_at', $date->year)
                    ->sum('plans.price'),
            ];
        }

        $recentMemberships = Membership::with(['user', 'plan'])
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get()
            ->map(fn ($m) => [
                'member' => $m->user?->name ?? '—',
                'email' => $m->user?->email ?? '—',
                'plan' => $m->plan?->name ?? '—',
                'status' => $m->status,
                'start_date' => $this->formatDate($m->start_date),
                'end_date' => $this->formatDate($m->end_date),
            ]);

        $expiringSoon = Membership::with(['user', 'plan'])
            ->where('status', 'active')
            ->whereBetween('end_date', [now(), now()->addDays(7)])
            ->orderBy('end_date')
            ->limit(10)
            ->get()
            ->map(fn ($m) => [
                'member' => $m->user?->name ?? '—',
                'plan' => $m->plan?->name ?? '—',
                'end_date' => $this->formatDate($m->end_date),
            ]);

        $activeCheckIns = Attendance::with('user')
            ->whereDate('check_in', Carbon::today())
            ->orderBy('check_in', 'desc')
            ->limit(20)
            ->get()
            ->map(fn ($a) => [
                'name' => $a->user?->name ?? '—',
                'check_in' => $this->formatDate($a->check_in, 'Y-m-d H:i'),
            ]);

        $orders = [
            'total' => 0,
            'revenue_total' => 0,
            'revenue_month' => 0,
            'by_status' => [],
        ];
        $recentOrders = collect();

        if (Schema::hasTable('orders')) {
            $orders = [
                'total' => Order::count(),
                'revenue_total' => (float) Order::sum('total_amount'),
                'revenue_month' => (float) Order::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('total_amount'),
                'by_status' => Order::selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status'),
            ];

            $recentOrders = Order::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(fn ($o) => [
                    'order_number' => $o->order_number ?? ('#' . $o->id),
                    'customer' => $o->customer_name ?: ($o->user?->name ?? '—'),
                    'total' => (float) $o->total_amount,
                    'status' => $o->status,
                    'date' => $this->formatDate($o->created_at),
                ]);
        }

        $subscriptions = [
            'active' => 0,
            'expired' => 0,
            'revenue_month' => 0,
        ];

        if (Schema::hasTable('subscriptions')) {
            $subscriptions = [
                'active' => Subscription::where('payment_status', 'paid')
                    ->where('end_date', '>=', now()->startOfDay())
                    ->count(),
                'expired' => Subscription::where('end_date', '<', now()->startOfDay())->count(),
                'revenue_month' => (float) Subscription::join('plans', 'subscriptions.plan_id', '=', 'plans.id')
                    ->whereMonth('subscriptions.created_at', now()->month)
                    ->whereYear('subscriptions.created_at', now()->year)
                    ->where('subscriptions.payment_status', 'paid')
                    ->sum('plans.price'),
            ];
        }

        $products = [
            'total' => 0,
            'active' => 0,
            'inactive' => 0,
            'low_stock' => 0,
        ];

        if (Schema::hasTable('products')) {
            $products = [
                'total' => Product::count(),
                'active' => Product::where('status', 'active')->count(),
                'inactive' => Product::where('status', 'inactive')->count(),
                'low_stock' => Product::where('status', 'active')->where('stock', '<=', 5)->count(),
            ];
        }

        $schedule = [
            'classes_this_week' => 0,
            'week_start' => $weekStart,
            'week_end' => Carbon::parse($weekStart)->addDays(6)->toDateString(),
        ];

        if (Schema::hasTable('schedules')) {
            $scheduleQuery = Schedule::query();
            if (Schema::hasColumn('schedules', 'week_start')) {
                $scheduleQuery->whereDate('week_start', $weekStart);
            }
            if (Schema::hasColumn('schedules', 'status')) {
                $scheduleQuery->where('status', 'active');
            }
            $schedule['classes_this_week'] = $scheduleQuery->count();
        }

        return response()->json([
            'generated_at' => now()->toIso8601String(),
            'report_period' => now()->format('F Y'),
            'stats' => $stats,
            'trends' => $trends,
            'recent_memberships' => $recentMemberships,
            'expiring_soon' => $expiringSoon,
            'active_check_ins' => $activeCheckIns,
            'orders' => $orders,
            'recent_orders' => $recentOrders->values()->all(),
            'subscriptions' => $subscriptions,
            'products' => $products,
            'schedule' => $schedule,
        ]);
    }
}