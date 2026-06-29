<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use App\Models\CoachDeliverable;
use App\Models\Meal;
use App\Models\NutritionLog;
use App\Models\User;
use App\Models\UserCoach;
use App\Models\UserWorkout;
use App\Services\PlanEntitlementService;
use App\Services\UserNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoachController extends Controller
{
    public function __construct(
        protected PlanEntitlementService $entitlements,
        protected UserNotificationService $notifications
    ) {}
    public function index()
    {
        $coaches = Coach::with('user')
            ->withCount('reviews as review_count')
            ->where('is_available', true)
            ->orderByDesc('rating')
            ->get()
            ->map(fn ($c) => $this->formatCoach($c));

        return response()->json($coaches);
    }

    public function show($id)
    {
        $coach = Coach::with('user')->find($id);
        if (!$coach) {
            return response()->json(['message' => 'Coach not found'], 404);
        }

        return response()->json($this->formatCoach($coach));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'name' => 'nullable|string|max:255',
            'specialization' => 'nullable|string',
            'bio' => 'nullable|string',
            'certifications' => 'nullable|string',
            'experience_years' => 'nullable|integer|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'avatar' => 'nullable|string',
            'expertise_areas' => 'nullable|array',
            'is_available' => 'nullable|boolean',
        ]);

        $data = $this->prepareCoachData($validated);

        if (!$data) {
            return response()->json([
                'message' => 'Enter a display name, or link this coach to a member account.',
                'errors' => ['name' => ['A coach name is required.']],
            ], 422);
        }

        $coach = Coach::create($data);

        return response()->json([
            'message' => 'Coach profile created successfully',
            'data' => $this->formatCoach($coach->load('user')),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $coach = Coach::find($id);
        if (!$coach) {
            return response()->json(['message' => 'Coach not found'], 404);
        }

        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'name' => 'nullable|string|max:255',
            'specialization' => 'nullable|string',
            'bio' => 'nullable|string',
            'certifications' => 'nullable|string',
            'experience_years' => 'nullable|integer|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'avatar' => 'nullable|string',
            'expertise_areas' => 'nullable|array',
            'is_available' => 'sometimes|boolean',
        ]);

        $data = $this->prepareCoachData($validated, $coach);
        if (!$data) {
            return response()->json([
                'message' => 'Enter a display name, or link this coach to a member account.',
                'errors' => ['name' => ['A coach name is required.']],
            ], 422);
        }

        $coach->update($data);

        return response()->json([
            'message' => 'Coach profile updated successfully',
            'data' => $this->formatCoach($coach->fresh()->load('user')),
        ]);
    }

    public function destroy($id)
    {
        $coach = Coach::find($id);
        if (!$coach) {
            return response()->json(['message' => 'Coach not found'], 404);
        }

        UserCoach::where('coach_id', $coach->id)
            ->where('status', 'active')
            ->update(['status' => 'ended', 'ended_at' => now()]);

        $coach->delete();

        return response()->json(['message' => 'Coach removed successfully']);
    }

    public function myCoach()
    {
        $assignment = $this->currentAssignment(Auth::id());

        if (!$assignment) {
            return response()->json(['assignment' => null]);
        }

        return response()->json([
            'assignment' => $this->formatAssignment($assignment),
        ]);
    }

    public function assignCoach(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'client' && !$this->entitlements->hasEntitlement($user, 'coaches_access')) {
            return response()->json(
                $this->entitlements->deniedResponse(
                    'coaches_access',
                    'Personal coaches are included with the Interstellar plan.'
                ),
                403
            );
        }

        $validated = $request->validate([
            'coach_id' => 'required|exists:coaches,id',
        ]);

        $coach = Coach::find($validated['coach_id']);
        if (!$coach || !$coach->is_available) {
            return response()->json(['message' => 'This coach is not available.'], 422);
        }

        $existing = UserCoach::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'active', 'leave_pending'])
            ->latest()
            ->first();

        if ($existing && $existing->status === 'leave_pending') {
            return response()->json([
                'message' => 'You have a pending leave request. Cancel it or wait for admin approval.',
            ], 409);
        }

        if ($existing) {
            if ($existing->coach_id == $coach->id) {
                $msg = $existing->status === 'active'
                    ? 'You are already assigned to this coach.'
                    : 'You already have a pending request for this coach.';
                return response()->json(['message' => $msg], 409);
            }

            if ($existing->status === 'active') {
                return response()->json([
                    'message' => 'You already have an active coach. End that assignment before requesting another.',
                ], 409);
            }

            $existing->update(['status' => 'cancelled', 'ended_at' => now()]);
        }

        $assignment = UserCoach::create([
            'user_id' => $user->id,
            'coach_id' => $coach->id,
            'status' => 'pending',
            'started_at' => null,
        ]);

        $assignment->load(['coach.user']);
        $this->notifications->notifyCoachRequestSubmitted($user->id, $assignment);

        return response()->json([
            'message' => 'Coach request submitted. An admin will review it shortly.',
            'assignment' => $this->formatAssignment($assignment->load(['coach.user'])),
        ], 201);
    }

    public function cancelRequest()
    {
        $user = Auth::user();

        $assignment = UserCoach::where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (!$assignment) {
            return response()->json(['message' => 'No pending request to cancel.'], 404);
        }

        $assignment->update(['status' => 'cancelled', 'ended_at' => now()]);

        return response()->json(['message' => 'Coach request cancelled.']);
    }

    public function endAssignment()
    {
        return $this->requestLeaveAssignment();
    }

    public function requestLeaveAssignment()
    {
        $user = Auth::user();

        $assignment = UserCoach::with('coach')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (!$assignment) {
            $pendingLeave = UserCoach::where('user_id', $user->id)
                ->where('status', 'leave_pending')
                ->exists();
            if ($pendingLeave) {
                return response()->json(['message' => 'You already have a pending leave request.'], 409);
            }

            return response()->json(['message' => 'No active coach assignment to leave.'], 404);
        }

        $assignment->update(['status' => 'leave_pending']);

        $assignment->load(['coach.user']);
        $this->notifications->notifyLeaveRequestSubmitted($user->id, $assignment);

        return response()->json([
            'message' => 'Leave request submitted. An admin will confirm before your assignment ends.',
            'assignment' => $this->formatAssignment($assignment->fresh()->load(['coach.user'])),
        ]);
    }

    public function cancelLeaveRequest()
    {
        $user = Auth::user();

        $assignment = UserCoach::where('user_id', $user->id)
            ->where('status', 'leave_pending')
            ->latest()
            ->first();

        if (!$assignment) {
            return response()->json(['message' => 'No pending leave request to cancel.'], 404);
        }

        $assignment->update(['status' => 'active']);

        return response()->json([
            'message' => 'Leave request cancelled. You remain assigned to your coach.',
            'assignment' => $this->formatAssignment($assignment->fresh()->load(['coach.user'])),
        ]);
    }

    public function changeCoach(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'client' && !$this->entitlements->hasEntitlement($user, 'coaches_access')) {
            return response()->json(
                $this->entitlements->deniedResponse(
                    'coaches_access',
                    'Personal coaches are included with the Interstellar plan.'
                ),
                403
            );
        }

        $validated = $request->validate([
            'coach_id' => 'required|exists:coaches,id',
        ]);

        $coach = Coach::find($validated['coach_id']);
        if (!$coach || !$coach->is_available) {
            return response()->json(['message' => 'This coach is not available.'], 422);
        }

        $active = UserCoach::where('user_id', $user->id)
            ->whereIn('status', ['active', 'leave_pending'])
            ->latest()
            ->first();

        if ($active && $active->status === 'leave_pending') {
            return response()->json([
                'message' => 'You have a pending leave request. Cancel it or wait for admin approval before changing coach.',
            ], 409);
        }

        if ($active && (int) $active->coach_id === (int) $coach->id) {
            return response()->json(['message' => 'You are already assigned to this coach.'], 409);
        }

        UserCoach::where('user_id', $user->id)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled', 'ended_at' => now()]);

        if ($active) {
            $active->update(['status' => 'ended', 'ended_at' => now()]);
            $active->coach?->decrement('clients_count');
        }

        $assignment = UserCoach::create([
            'user_id' => $user->id,
            'coach_id' => $coach->id,
            'status' => 'pending',
            'started_at' => null,
        ]);

        $assignment->load(['coach.user']);
        $this->notifications->notifyCoachRequestSubmitted($user->id, $assignment);

        $message = $active
            ? 'Coach change request submitted. An admin will review it shortly.'
            : 'Coach request submitted. An admin will review it shortly.';

        return response()->json([
            'message' => $message,
            'assignment' => $this->formatAssignment($assignment->load(['coach.user'])),
        ], 201);
    }

    public function isStaff()
    {
        $user = Auth::user();
        $coach = Coach::where('user_id', $user->id)->first();

        return response()->json([
            'is_coach' => $user->role === 'coach' || (bool) $coach,
            'coach_id' => $coach?->id,
            'is_staff_pass' => $user->role === 'coach',
        ]);
    }

    public function myClients()
    {
        $coach = $this->resolveCoachForViewer();
        if (!$coach) {
            return response()->json(['message' => 'Coach profile not found'], 403);
        }

        $clients = UserCoach::with('user')
            ->where('coach_id', $coach->id)
            ->whereIn('status', ['active', 'leave_pending'])
            ->orderByDesc('started_at')
            ->get()
            ->map(fn ($a) => [
                'assignment_id' => $a->id,
                'user_id' => $a->user_id,
                'name' => $a->user?->name,
                'email' => $a->user?->email,
                'started_at' => $a->started_at?->format('Y-m-d'),
            ]);

        return response()->json(['clients' => $clients]);
    }

    public function clientNutrition(Request $request, $userId)
    {
        $member = User::find($userId);
        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        if (!$this->canViewMember($member->id)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $dateStr = $request->filled('date')
            ? Carbon::parse($request->input('date'))->toDateString()
            : now()->toDateString();

        $log = NutritionLog::where('user_id', $member->id)
            ->where('log_date', $dateStr)
            ->first();

        $meals = $log
            ? Meal::where('nutrition_log_id', $log->id)->orderBy('created_at')->get()
            : collect();

        return response()->json([
            'user_id' => $member->id,
            'log_date' => $dateStr,
            'calories' => (int) ($log?->calories ?? 0),
            'protein_g' => (int) ($log?->protein_g ?? 0),
            'carbs_g' => (int) ($log?->carbs_g ?? 0),
            'fats_g' => (int) ($log?->fats_g ?? 0),
            'water_ml' => (int) ($log?->water_ml ?? 0),
            'meals' => $meals,
        ]);
    }

    public function clientWorkouts($userId)
    {
        $member = User::find($userId);
        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        if (!$this->canViewMember($member->id)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $sessions = UserWorkout::with('workout')
            ->where('user_id', $member->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return response()->json(['workouts' => $sessions]);
    }

    public function clientDeliverables($userId)
    {
        $member = User::find($userId);
        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        if (!$this->canViewMember($member->id)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $coach = $this->requireCoachProfile();
        if (!$coach) {
            return response()->json(['message' => 'Coach profile not found'], 403);
        }

        $items = CoachDeliverable::where('coach_id', $coach->id)
            ->where('client_user_id', $member->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn (CoachDeliverable $d) => $this->formatDeliverable($d));

        return response()->json(['deliverables' => $items]);
    }

    public function sendDeliverable(Request $request, $userId)
    {
        $member = User::find($userId);
        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        if (!$this->canViewMember($member->id)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'type' => 'required|in:message,program',
            'title' => 'nullable|string|max:255',
            'body' => 'required|string|max:10000',
            'program_details' => 'nullable|array',
            'program_details.workout_plan' => 'nullable|string|max:5000',
            'program_details.nutrition_plan' => 'nullable|string|max:5000',
            'program_details.notes' => 'nullable|string|max:2000',
        ]);

        if ($validated['type'] === 'program' && empty(trim((string) ($validated['title'] ?? '')))) {
            return response()->json([
                'message' => 'Program title is required.',
                'errors' => ['title' => ['Program title is required.']],
            ], 422);
        }

        $coach = $this->requireCoachProfile();
        if (!$coach) {
            return response()->json(['message' => 'Coach profile not found'], 403);
        }

        $deliverable = CoachDeliverable::create([
            'coach_id' => $coach->id,
            'client_user_id' => $member->id,
            'sender_user_id' => Auth::id(),
            'type' => $validated['type'],
            'title' => $validated['title'] ?? null,
            'body' => $validated['body'],
            'program_details' => $validated['program_details'] ?? null,
        ]);

        $this->notifications->notifyCoachDeliverable($member->id, $deliverable, $coach);

        return response()->json([
            'message' => $validated['type'] === 'program'
                ? 'Program sent to client.'
                : 'Message sent to client.',
            'deliverable' => $this->formatDeliverable($deliverable),
        ], 201);
    }

    public function sendMessageToCoach(Request $request)
    {
        $user = Auth::user();
        $assignment = $this->clientCoachAssignment($user->id);

        if (!$assignment) {
            return response()->json(['message' => 'No active coach assignment.'], 404);
        }

        if ($user->role === 'client' && !$this->entitlements->hasEntitlement($user, 'coaches_access')) {
            return response()->json(
                $this->entitlements->deniedResponse(
                    'coaches_access',
                    'Personal coaches are included with the Interstellar plan.'
                ),
                403
            );
        }

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $coach = Coach::with('user')->find($assignment->coach_id);
        if (!$coach) {
            return response()->json(['message' => 'Coach not found'], 404);
        }

        $deliverable = CoachDeliverable::create([
            'coach_id' => $coach->id,
            'client_user_id' => $user->id,
            'sender_user_id' => $user->id,
            'type' => 'message',
            'body' => trim($validated['body']),
        ]);

        if ($coach->user_id) {
            $this->notifications->notifyClientMessageToCoach($coach->user_id, $deliverable, $user);
        }

        return response()->json([
            'message' => 'Message sent to your coach.',
            'deliverable' => $this->formatDeliverable($deliverable->load('sender')),
        ], 201);
    }

    public function myInbox()
    {
        $user = Auth::user();
        $assignment = $this->clientCoachAssignment($user->id);

        if (!$assignment) {
            return response()->json([
                'assignment' => null,
                'deliverables' => [],
            ]);
        }

        $rows = CoachDeliverable::with('coach.user')
            ->where('coach_id', $assignment->coach_id)
            ->where('client_user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $items = $rows->map(fn (CoachDeliverable $d) => $this->formatDeliverable($d));

        $unreadCount = $rows->filter(
            fn (CoachDeliverable $d) => !$d->read_at && !$this->isFromClient($d)
        )->count();

        return response()->json([
            'assignment' => $this->formatAssignment($assignment),
            'deliverables' => $items,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markDeliverableRead($id)
    {
        $user = Auth::user();
        $deliverable = CoachDeliverable::with('coach')->find($id);

        if (!$deliverable) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $coachProfile = Coach::where('user_id', $user->id)->first();
        $isClient = (int) $deliverable->client_user_id === (int) $user->id;
        $isCoach = $coachProfile && (int) $deliverable->coach_id === (int) $coachProfile->id;

        if ($isClient && $this->isFromClient($deliverable)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($isCoach && !$this->isFromClient($deliverable)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (!$isClient && !$isCoach && !$user->isAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (!$deliverable->read_at) {
            $deliverable->update(['read_at' => now()]);
        }

        return response()->json([
            'message' => 'Marked as read.',
            'deliverable' => $this->formatDeliverable($deliverable->fresh()->load('sender')),
        ]);
    }

    public function adminIndex()
    {
        $coaches = Coach::with([
            'user',
            'clients' => fn ($q) => $q->whereIn('status', ['active', 'leave_pending'])->with('user')->orderByDesc('started_at'),
        ])
            ->withCount([
                'clients as active_clients_count' => fn ($q) => $q->whereIn('status', ['active', 'leave_pending']),
                'clients as pending_clients_count' => fn ($q) => $q->where('status', 'pending'),
                'clients as leave_pending_count' => fn ($q) => $q->where('status', 'leave_pending'),
                'reviews as review_count',
            ])
            ->orderByDesc('active_clients_count')
            ->orderBy('name')
            ->get()
            ->map(function (Coach $c) {
                $activeClients = $c->clients->map(fn (UserCoach $a) => [
                    'assignment_id' => $a->id,
                    'user_id' => $a->user_id,
                    'name' => $a->user?->name,
                    'email' => $a->user?->email,
                    'started_at' => $a->started_at?->format('Y-m-d'),
                ])->values();

                return array_merge($this->formatCoach($c), [
                    'clients_count' => $c->active_clients_count,
                    'active_clients_count' => $c->active_clients_count,
                    'pending_clients_count' => $c->pending_clients_count,
                    'active_clients' => $activeClients,
                ]);
            });

        $summary = [
            'total_coaches' => $coaches->count(),
            'total_active_clients' => $coaches->sum('active_clients_count'),
            'total_pending_requests' => $coaches->sum('pending_clients_count'),
            'total_leave_requests' => UserCoach::where('status', 'leave_pending')->count(),
        ];

        return response()->json([
            'coaches' => $coaches,
            'summary' => $summary,
        ]);
    }

    public function adminRequests()
    {
        $requests = UserCoach::with(['user', 'coach.user'])
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($a) => $this->formatAssignment($a));

        return response()->json(['requests' => $requests]);
    }

    public function adminApprove($id)
    {
        $assignment = UserCoach::with('coach')->find($id);
        if (!$assignment || $assignment->status !== 'pending') {
            return response()->json(['message' => 'Pending request not found'], 404);
        }

        UserCoach::where('user_id', $assignment->user_id)
            ->where('status', 'active')
            ->where('id', '!=', $assignment->id)
            ->get()
            ->each(function (UserCoach $old) {
                $old->update(['status' => 'ended', 'ended_at' => now()]);
                Coach::where('id', $old->coach_id)->decrement('clients_count');
            });

        $assignment->update([
            'status' => 'active',
            'started_at' => now(),
            'ended_at' => null,
        ]);

        $assignment->coach?->increment('clients_count');

        $assignment->load(['user', 'coach.user']);
        if ($assignment->user_id) {
            $this->notifications->notifyCoachRequestApproved((int) $assignment->user_id, $assignment);
        }
        if ($assignment->coach?->user_id && $assignment->user) {
            $this->notifications->notifyNewClientAssignedToCoach(
                (int) $assignment->coach->user_id,
                $assignment->user,
                $assignment
            );
        }

        return response()->json([
            'message' => 'Coach assignment approved.',
            'assignment' => $this->formatAssignment($assignment->fresh()->load(['user', 'coach.user'])),
        ]);
    }

    public function adminReject($id)
    {
        $assignment = UserCoach::find($id);
        if (!$assignment || $assignment->status !== 'pending') {
            return response()->json(['message' => 'Pending request not found'], 404);
        }

        $assignment->update(['status' => 'rejected', 'ended_at' => now()]);

        $assignment->load(['user', 'coach.user']);
        if ($assignment->user_id) {
            $this->notifications->notifyCoachRequestRejected((int) $assignment->user_id, $assignment);
        }

        return response()->json(['message' => 'Coach request rejected.']);
    }

    public function adminLeaveRequests()
    {
        $requests = UserCoach::with(['user', 'coach.user'])
            ->where('status', 'leave_pending')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn ($a) => $this->formatAssignment($a));

        return response()->json(['requests' => $requests]);
    }

    public function adminApproveLeave($id)
    {
        $assignment = UserCoach::with('coach')->find($id);
        if (!$assignment || $assignment->status !== 'leave_pending') {
            return response()->json(['message' => 'Pending leave request not found'], 404);
        }

        $assignment->update(['status' => 'ended', 'ended_at' => now()]);
        $assignment->coach?->decrement('clients_count');

        $assignment->load(['user', 'coach.user']);
        if ($assignment->user_id) {
            $this->notifications->notifyLeaveRequestApproved((int) $assignment->user_id, $assignment);
        }

        return response()->json([
            'message' => 'Leave request approved. Member is no longer assigned to this coach.',
            'assignment' => $this->formatAssignment($assignment->fresh()->load(['user', 'coach.user'])),
        ]);
    }

    public function adminRejectLeave($id)
    {
        $assignment = UserCoach::find($id);
        if (!$assignment || $assignment->status !== 'leave_pending') {
            return response()->json(['message' => 'Pending leave request not found'], 404);
        }

        $assignment->update(['status' => 'active']);

        $assignment->load(['user', 'coach.user']);
        if ($assignment->user_id) {
            $this->notifications->notifyLeaveRequestRejected((int) $assignment->user_id, $assignment);
        }

        return response()->json([
            'message' => 'Leave request rejected. Member remains assigned to their coach.',
            'assignment' => $this->formatAssignment($assignment->fresh()->load(['user', 'coach.user'])),
        ]);
    }

    public function adminEndAssignment($id)
    {
        $assignment = UserCoach::with('coach')->find($id);
        if (!$assignment || !in_array($assignment->status, ['active', 'leave_pending'], true)) {
            return response()->json(['message' => 'Active assignment not found'], 404);
        }

        $assignment->update(['status' => 'ended', 'ended_at' => now()]);
        $assignment->coach?->decrement('clients_count');

        $assignment->load(['user', 'coach.user']);
        if ($assignment->user_id) {
            $this->notifications->notifyCoachAssignmentEnded((int) $assignment->user_id, $assignment);
        }

        return response()->json(['message' => 'Assignment ended.']);
    }

    public function adminMemberAssignment($userId)
    {
        $member = User::find($userId);
        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        $assignment = UserCoach::with(['coach.user'])
            ->where('user_id', $member->id)
            ->whereIn('status', ['pending', 'active', 'leave_pending'])
            ->orderByRaw("FIELD(status, 'active', 'leave_pending', 'pending')")
            ->latest()
            ->first();

        return response()->json([
            'user' => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
            ],
            'assignment' => $assignment ? $this->formatAssignment($assignment) : null,
        ]);
    }

    public function adminAssignClient(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'coach_id' => 'required|exists:coaches,id',
        ]);

        $member = User::find($validated['user_id']);
        if (!$member || $member->role !== 'client') {
            return response()->json(['message' => 'Only client members can be assigned to a coach.'], 422);
        }

        $coach = Coach::find($validated['coach_id']);
        if (!$coach) {
            return response()->json(['message' => 'Coach not found'], 404);
        }

        $pending = UserCoach::where('user_id', $member->id)
            ->where('status', 'pending')
            ->get();
        foreach ($pending as $p) {
            $p->update(['status' => 'cancelled', 'ended_at' => now()]);
        }

        $activeSame = UserCoach::where('user_id', $member->id)
            ->where('status', 'active')
            ->where('coach_id', $coach->id)
            ->first();

        if ($activeSame) {
            return response()->json([
                'message' => 'Member is already assigned to this coach.',
                'assignment' => $this->formatAssignment($activeSame->load(['coach.user'])),
            ]);
        }

        UserCoach::where('user_id', $member->id)
            ->whereIn('status', ['active', 'leave_pending'])
            ->get()
            ->each(function (UserCoach $old) {
                $old->update(['status' => 'ended', 'ended_at' => now()]);
                Coach::where('id', $old->coach_id)->decrement('clients_count');
            });

        $assignment = UserCoach::create([
            'user_id' => $member->id,
            'coach_id' => $coach->id,
            'status' => 'active',
            'started_at' => now(),
            'ended_at' => null,
        ]);

        $coach->increment('clients_count');

        $assignment->load(['user', 'coach.user']);
        if ($member->id) {
            $this->notifications->notifyCoachAssignedByAdmin($member->id, $assignment);
        }
        if ($coach->user_id) {
            $this->notifications->notifyNewClientAssignedToCoach((int) $coach->user_id, $member, $assignment);
        }

        return response()->json([
            'message' => 'Member assigned to coach.',
            'assignment' => $this->formatAssignment($assignment->load(['user', 'coach.user'])),
        ], 201);
    }

    protected function clientCoachAssignment(int $userId): ?UserCoach
    {
        return UserCoach::with(['coach.user'])
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'leave_pending'])
            ->orderByRaw("FIELD(status, 'active', 'leave_pending')")
            ->latest()
            ->first();
    }

    protected function currentAssignment(int $userId): ?UserCoach
    {
        return UserCoach::with(['coach.user'])
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'active', 'leave_pending'])
            ->orderByRaw("FIELD(status, 'active', 'leave_pending', 'pending')")
            ->latest()
            ->first();
    }

    protected function resolveCoachForViewer(): ?Coach
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return null;
        }

        return Coach::where('user_id', $user->id)->first();
    }

    protected function requireCoachProfile(): ?Coach
    {
        return $this->resolveCoachForViewer();
    }

    protected function canViewMember(int $memberId): bool
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return true;
        }

        $coach = Coach::where('user_id', $user->id)->first();
        if (!$coach) {
            return false;
        }

        return UserCoach::where('coach_id', $coach->id)
            ->where('user_id', $memberId)
            ->whereIn('status', ['active', 'leave_pending'])
            ->exists();
    }

    protected function isFromClient(CoachDeliverable $deliverable): bool
    {
        return $deliverable->sender_user_id !== null
            && (int) $deliverable->sender_user_id === (int) $deliverable->client_user_id;
    }

    protected function formatDeliverable(CoachDeliverable $deliverable): array
    {
        $fromClient = $this->isFromClient($deliverable);

        return [
            'id' => $deliverable->id,
            'type' => $deliverable->type,
            'title' => $deliverable->title,
            'body' => $deliverable->body,
            'program_details' => $deliverable->program_details,
            'read_at' => $deliverable->read_at?->toIso8601String(),
            'is_read' => (bool) $deliverable->read_at,
            'created_at' => $deliverable->created_at?->toIso8601String(),
            'coach_id' => $deliverable->coach_id,
            'client_user_id' => $deliverable->client_user_id,
            'sender_user_id' => $deliverable->sender_user_id,
            'from_client' => $fromClient,
            'from_coach' => !$fromClient,
        ];
    }

    protected function prepareCoachData(array $validated, ?Coach $existing = null): ?array
    {
        $data = collect($validated)->only([
            'user_id', 'name', 'specialization', 'bio', 'certifications',
            'experience_years', 'hourly_rate', 'avatar', 'expertise_areas', 'is_available',
        ])->all();

        if (array_key_exists('user_id', $data) && ($data['user_id'] === '' || $data['user_id'] === null)) {
            $data['user_id'] = null;
        }

        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '' && !empty($data['user_id'])) {
            $name = User::find($data['user_id'])?->name ?? '';
        }
        if ($name === '' && $existing) {
            $name = $existing->name ?? $existing->user?->name ?? '';
        }

        if ($name === '') {
            return null;
        }

        $data['name'] = $name;

        if (!array_key_exists('is_available', $data)) {
            $data['is_available'] = true;
        }

        return $data;
    }

    protected function formatCoach(Coach $coach): array
    {
        $data = $coach->toArray();
        $data['display_name'] = $coach->user?->name ?? $coach->name ?? 'Coach #' . $coach->id;

        return $data;
    }

    protected function formatAssignment(UserCoach $assignment): array
    {
        $coach = $assignment->coach;

        return [
            'id' => $assignment->id,
            'status' => $assignment->status,
            'started_at' => $assignment->started_at?->format('Y-m-d'),
            'ended_at' => $assignment->ended_at?->format('Y-m-d'),
            'created_at' => $assignment->created_at?->toIso8601String(),
            'user' => $assignment->user ? [
                'id' => $assignment->user->id,
                'name' => $assignment->user->name,
                'email' => $assignment->user->email,
            ] : null,
            'coach' => $coach ? $this->formatCoach($coach->loadMissing('user')) : null,
        ];
    }
}
