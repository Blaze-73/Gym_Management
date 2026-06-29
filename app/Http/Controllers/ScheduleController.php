<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use App\Models\Schedule;
use App\Services\PlanEntitlementService;
use App\Services\UserNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ScheduleController extends Controller
{
    public function __construct(
        protected PlanEntitlementService $entitlements,
        protected UserNotificationService $notifications
    ) {}
    private const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    private function normalizeWeekStart(?string $value): string
    {
        $date = $value
            ? Carbon::parse($value)
            : Carbon::now();

        return $date->startOfWeek(Carbon::MONDAY)->toDateString();
    }

    private function baseRules(): array
    {
        return [
            'class_name' => 'required|string|max:255',
            'day_of_week' => ['required', 'string', Rule::in(self::DAYS)],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'capacity' => 'required|integer|min:1',
            'room' => 'nullable|string|max:255',
            'coach_id' => 'nullable|exists:coaches,id',
            'status' => 'required|in:active,inactive',
            'week_start' => 'required|date',
        ];
    }

    private function formatSchedule(Schedule $schedule, ?int $myCoachId = null): array
    {
        $schedule->loadMissing('coach.user');

        $coach = $schedule->coach;
        $coachName = null;
        if ($coach) {
            $coachName = $coach->user?->name ?? $coach->name ?? 'Coach #' . $coach->id;
        }

        $start = $this->formatTime($schedule->start_time);
        $end = $this->formatTime($schedule->end_time);
        if ($end === '00:00' || ($start && $end && $end <= $start)) {
            $end = null;
        }

        $weekStart = $schedule->week_start
            ? Carbon::parse($schedule->week_start)->toDateString()
            : null;
        $weekEnd = $weekStart
            ? Carbon::parse($weekStart)->addDays(6)->toDateString()
            : null;

        $isMine = $myCoachId !== null
            && $schedule->coach_id !== null
            && (int) $schedule->coach_id === (int) $myCoachId;

        return [
            'id' => $schedule->id,
            'class_name' => $schedule->class_name,
            'day_of_week' => $schedule->day_of_week,
            'start_time' => $start,
            'end_time' => $end,
            'capacity' => (int) $schedule->capacity,
            'room' => $schedule->room,
            'status' => $schedule->status ?? 'active',
            'coach_id' => $schedule->coach_id,
            'coach_name' => $coachName,
            'coach_avatar' => $coach?->avatar,
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'is_my_assignment' => $isMine,
            'created_at' => $schedule->created_at,
            'updated_at' => $schedule->updated_at,
        ];
    }

    private function coachIdForUser(?int $userId): ?int
    {
        if (!$userId) {
            return null;
        }

        return Coach::where('user_id', $userId)->value('id');
    }

    private function notifyCoachIfAssigned(Schedule $schedule, ?int $previousCoachId): void
    {
        if (!$schedule->coach_id || (int) $schedule->coach_id === (int) $previousCoachId) {
            return;
        }

        $coach = Coach::with('user')->find($schedule->coach_id);
        if (!$coach?->user_id) {
            return;
        }

        $this->notifications->notifyCoachClassAssignment((int) $coach->user_id, $schedule);
    }

    private function formatTime($value): string
    {
        if (!$value) {
            return '';
        }

        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Exception) {
            return substr((string) $value, 0, 5);
        }
    }

    private function queryOrdered(?string $weekStart = null)
    {
        $order = implode("', '", self::DAYS);

        $query = Schedule::with('coach.user');

        if ($weekStart) {
            $query->whereDate('week_start', $this->normalizeWeekStart($weekStart));
        }

        return $query
            ->orderByDesc('week_start')
            ->orderByRaw("FIELD(day_of_week, '$order')")
            ->orderBy('start_time');
    }

    /** Active classes for a given week (default: current week). */
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user && $user->role === 'client' && !$this->entitlements->hasEntitlement($user, 'schedule_access')) {
            return response()->json(
                $this->entitlements->deniedResponse(
                    'schedule_access',
                    'Class schedules are included with the Interstellar plan only.'
                ),
                403
            );
        }

        $week = $this->normalizeWeekStart($request->query('week_start'));
        $myCoachId = $this->coachIdForUser($user?->id);

        $schedules = $this->queryOrdered($week)
            ->where('status', 'active')
            ->get()
            ->map(fn ($s) => $this->formatSchedule($s, $myCoachId));

        return response()->json($schedules);
    }

    /** All classes for admin (optional week filter). */
    public function adminIndex(Request $request)
    {
        $week = $request->query('week_start')
            ? $this->normalizeWeekStart($request->query('week_start'))
            : null;

        $schedules = $this->queryOrdered($week)
            ->get()
            ->map(fn ($s) => $this->formatSchedule($s));

        return response()->json($schedules);
    }

    /** Classes assigned to the logged-in coach for a week. */
    public function coachClasses(Request $request)
    {
        $coach = Coach::where('user_id', Auth::id())->first();

        if (!$coach) {
            return response()->json([]);
        }

        $week = $this->normalizeWeekStart($request->query('week_start'));

        $schedules = $this->queryOrdered($week)
            ->where('coach_id', $coach->id)
            ->where('status', 'active')
            ->get()
            ->map(fn ($s) => $this->formatSchedule($s, $coach->id));

        return response()->json($schedules);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->baseRules());
        $validated['week_start'] = $this->normalizeWeekStart($validated['week_start']);

        $schedule = Schedule::create($validated);
        $this->notifyCoachIfAssigned($schedule, null);

        return response()->json([
            'message' => 'Class scheduled successfully',
            'data' => $this->formatSchedule($schedule),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $schedule = Schedule::find($id);
        if (!$schedule) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'class_name' => 'sometimes|required|string|max:255',
            'day_of_week' => ['sometimes', 'required', 'string', Rule::in(self::DAYS)],
            'start_time' => 'sometimes|required|date_format:H:i',
            'end_time' => 'sometimes|required|date_format:H:i',
            'capacity' => 'sometimes|required|integer|min:1',
            'room' => 'nullable|string|max:255',
            'coach_id' => 'nullable|exists:coaches,id',
            'status' => 'sometimes|required|in:active,inactive',
            'week_start' => 'sometimes|required|date',
        ]);

        if (isset($validated['week_start'])) {
            $validated['week_start'] = $this->normalizeWeekStart($validated['week_start']);
        }

        if (isset($validated['start_time'], $validated['end_time'])) {
            if (strtotime($validated['end_time']) <= strtotime($validated['start_time'])) {
                return response()->json([
                    'message' => 'End time must be after start time.',
                    'errors' => ['end_time' => ['End time must be after start time.']],
                ], 422);
            }
        } elseif (isset($validated['end_time'])) {
            $start = $validated['start_time'] ?? $this->formatTime($schedule->start_time);
            if (strtotime($validated['end_time']) <= strtotime($start)) {
                return response()->json([
                    'message' => 'End time must be after start time.',
                    'errors' => ['end_time' => ['End time must be after start time.']],
                ], 422);
            }
        }

        $previousCoachId = $schedule->coach_id;

        $schedule->update($validated);
        $schedule->refresh();
        $this->notifyCoachIfAssigned($schedule, $previousCoachId);

        return response()->json([
            'message' => 'Updated successfully',
            'data' => $this->formatSchedule($schedule->fresh()),
        ]);
    }

    public function destroy($id)
    {
        $schedule = Schedule::find($id);
        if (!$schedule) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $schedule->delete();

        return response()->json(['message' => 'Class deleted successfully']);
    }
}
