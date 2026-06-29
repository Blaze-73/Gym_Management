<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Services\GymCheckinService;
use App\Services\PlanEntitlementService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        protected PlanEntitlementService $entitlements,
        protected GymCheckinService $gymCheckin
    ) {
    }

    /**
     * Admin: printable gym QR code (one code for the whole gym).
     */
    public function gymQrCode()
    {
        $token = $this->gymCheckin->token();
        $url = $this->gymCheckin->checkinUrl();

        return response()->json([
            'token' => $token,
            'url' => $url,
            'payload' => $url,
            'short_payload' => $this->gymCheckin->qrPayloadShort(),
            'permanent' => true,
            'instructions' => 'This is your permanent gym QR code. It stays the same forever (admin logout does not change it). Download, print once, and place it at the entrance.',
        ]);
    }

    /**
     * Regenerating is disabled — one permanent QR for the gym.
     */
    public function regenerateGymQr()
    {
        return response()->json([
            'message' => 'The gym QR code is permanent and cannot be regenerated. Your existing printed poster will keep working.',
        ], 403);
    }

    /**
     * Client: scan the gym QR — one check-in per member per calendar day.
     */
    public function scanGym(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string|max:512',
        ]);

        $token = $this->parseGymToken($validated['token']);
        if (!$this->gymCheckin->isValid($token)) {
            return response()->json(['message' => 'Invalid gym QR code.'], 422);
        }

        return $this->performDailyCheckIn($request->user());
    }

    /**
     * Client: today's check-in status.
     */
    public function today(Request $request)
    {
        $user = $request->user();
        $attendance = $this->todaysAttendance($user->id);

        return response()->json([
            'checked_in_today' => $attendance !== null,
            'attendance' => $attendance,
            'date' => Carbon::today()->toDateString(),
        ]);
    }

    /**
     * Manual check-in (same rules as gym QR scan).
     */
    public function checkIn(Request $request)
    {
        return $this->performDailyCheckIn($request->user());
    }

    /**
     * Check-out is optional; daily attendance is counted on check-in only.
     */
    public function checkOut(Request $request)
    {
        $user = $request->user();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('check_in', Carbon::today())
            ->whereNull('check_out')
            ->first();

        if (!$attendance) {
            return response()->json([
                'message' => 'No open visit found for today.',
            ], 422);
        }

        $checkOut = now();
        $duration = $attendance->check_in->diffInMinutes($checkOut);

        $attendance->update([
            'check_out' => $checkOut,
            'duration_minutes' => $duration,
        ]);

        return response()->json([
            'message' => 'Checked out successfully',
            'attendance' => $attendance,
            'duration' => $this->formatDuration($duration),
        ]);
    }

    /**
     * Daily attendance summary for admin dashboard.
     */
    public function dailyStats(Request $request)
    {
        $days = min(max((int) $request->get('days', 14), 7), 60);
        $start = Carbon::today()->subDays($days - 1)->startOfDay();
        $end = Carbon::today()->endOfDay();

        $rows = Attendance::query()
            ->selectRaw('DATE(check_in) as day')
            ->selectRaw('COUNT(DISTINCT user_id) as unique_members')
            ->whereBetween('check_in', [$start, $end])
            ->groupBy('day')
            ->orderByDesc('day')
            ->get()
            ->keyBy('day');

        $daily = [];
        for ($i = 0; $i < $days; $i++) {
            $date = Carbon::today()->subDays($i);
            $key = $date->toDateString();
            $row = $rows->get($key);

            $daily[] = [
                'date' => $key,
                'label' => $date->format('D, M j'),
                'unique_members' => (int) ($row->unique_members ?? 0),
                'total_checkins' => (int) ($row->unique_members ?? 0),
            ];
        }

        $selectedDate = $request->get('date', Carbon::today()->toDateString());
        $dayStart = Carbon::parse($selectedDate)->startOfDay();
        $dayEnd = Carbon::parse($selectedDate)->endOfDay();

        $dayVisits = Attendance::with('user')
            ->whereBetween('check_in', [$dayStart, $dayEnd])
            ->orderByDesc('check_in')
            ->get()
            ->map(fn (Attendance $a) => [
                'id' => $a->id,
                'member' => $a->user?->name ?? 'Member',
                'email' => $a->user?->email,
                'check_in' => $a->check_in?->format('g:i A'),
                'check_out' => $a->check_out?->format('g:i A'),
                'duration' => $a->check_out ? $this->formatDuration($a->duration_minutes) : 'Present',
                'status' => 'checked_in',
            ]);

        $todayCheckedIn = Attendance::with('user')
            ->whereDate('check_in', Carbon::today())
            ->orderByDesc('check_in')
            ->get()
            ->map(fn (Attendance $a) => [
                'id' => $a->id,
                'member' => $a->user?->name ?? 'Member',
                'check_in' => $a->check_in?->format('g:i A'),
            ]);

        return response()->json([
            'daily' => $daily,
            'selected_date' => $selectedDate,
            'selected_visits' => $dayVisits,
            'today_checked_in' => [
                'count' => $todayCheckedIn->count(),
                'members' => $todayCheckedIn,
            ],
            'summary' => [
                'today_unique' => (int) ($rows->get(Carbon::today()->toDateString())?->unique_members ?? 0),
                'week_unique' => (int) Attendance::where('check_in', '>=', Carbon::today()->subDays(6)->startOfDay())
                    ->select('user_id')
                    ->distinct()
                    ->count('user_id'),
            ],
        ]);
    }

    public function history(Request $request)
    {
        $user = $request->user();

        $attendances = Attendance::where('user_id', $user->id)
            ->orderBy('check_in', 'desc')
            ->paginate(20);

        $stats = [
            'total_visits' => Attendance::where('user_id', $user->id)->count(),
            'this_month_visits' => Attendance::where('user_id', $user->id)
                ->whereMonth('check_in', now()->month)
                ->whereYear('check_in', now()->year)
                ->count(),
            'total_time_minutes' => Attendance::where('user_id', $user->id)
                ->whereNotNull('check_out')
                ->sum('duration_minutes'),
        ];

        $stats['total_time_formatted'] = $this->formatDuration($stats['total_time_minutes']);

        return response()->json([
            'attendances' => $attendances,
            'stats' => $stats,
        ]);
    }

    public function index(Request $request)
    {
        $query = Attendance::with('user');

        if ($request->has('date')) {
            $query->whereDate('check_in', $request->date);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        return response()->json(
            $query->orderBy('check_in', 'desc')->paginate(50)
        );
    }

    /**
     * Members who checked in today.
     */
    public function active()
    {
        $today = Attendance::with('user')
            ->whereDate('check_in', Carbon::today())
            ->orderByDesc('check_in')
            ->get();

        return response()->json([
            'active_count' => $today->count(),
            'active_users' => $today,
        ]);
    }

    private function performDailyCheckIn(User $user)
    {
        if (!$this->userHasGymAccess($user)) {
            return response()->json([
                'message' => 'No active membership or subscription found.',
            ], 403);
        }

        $existing = $this->todaysAttendance($user->id);
        if ($existing) {
            return response()->json([
                'action' => 'already_checked_in',
                'message' => 'You already checked in today. Come back tomorrow!',
                'attendance' => $existing,
                'checked_in_at' => $existing->check_in?->format('g:i A'),
            ], 422);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'check_in' => now(),
        ]);

        return response()->json([
            'action' => 'check_in',
            'message' => 'Checked in for today. See you next time!',
            'attendance' => $attendance,
            'checked_in_at' => $attendance->check_in?->format('g:i A'),
        ], 201);
    }

    private function todaysAttendance(int $userId): ?Attendance
    {
        return Attendance::where('user_id', $userId)
            ->whereDate('check_in', Carbon::today())
            ->first();
    }

    private function userHasGymAccess(User $user): bool
    {
        return $this->entitlements->activePlanForUser($user) !== null;
    }

    private function parseGymToken(string $raw): ?string
    {
        $value = trim($raw);

        if (preg_match('/[?&]t=([a-f0-9]{32})/i', $value, $matches)) {
            return strtolower($matches[1]);
        }

        if (preg_match('/ALIENGYM:GYM:([a-f0-9]{32})/i', $value, $matches)) {
            return strtolower($matches[1]);
        }

        if (preg_match('/^([a-f0-9]{32})$/i', $value, $matches)) {
            return strtolower($matches[1]);
        }

        return null;
    }

    private function formatDuration($minutes): string
    {
        if (!$minutes) {
            return '0 minutes';
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$mins}m";
        }

        return "{$mins}m";
    }
}
