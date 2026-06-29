<?php

namespace App\Http\Controllers;

use App\Models\UserWorkout;
use App\Models\Workout;
use App\Services\UserNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserWorkoutController extends Controller
{
    public function __construct(protected UserNotificationService $notifications) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->input('status', 'all');

        $query = UserWorkout::with('workout')
            ->where('user_id', $user->id);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $workouts = $query->orderBy('created_at', 'desc')->get();
        return response()->json($workouts);
    }

    public function start(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'workout_id' => 'required|exists:workouts,id',
        ]);

        $userWorkout = UserWorkout::create([
            'user_id' => $user->id,
            'workout_id' => $validated['workout_id'],
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        return response()->json([
            'message' => 'Workout started',
            'data' => $userWorkout
        ], 201);
    }
    // Add this inside UserWorkoutController.php

public function updateProgress(Request $request, $id)
{
    $user = Auth::user();
    // Find the specific session for this user
    $userWorkout = UserWorkout::where('user_id', $user->id)->find($id);

    if (!$userWorkout) {
        return response()->json(['message' => 'Workout session not found'], 404);
    }

    $validated = $request->validate([
        'completed_exercise_index' => 'required|integer',
        'elapsed_time' => 'nullable|integer',
    ]);

    // We can save the progress in the notes or a specific column if you have one
    // For now, we'll ensure the workout is marked as 'in_progress'
    $userWorkout->update([
        'status' => 'in_progress',
        'notes' => 'Last completed exercise index: ' . $validated['completed_exercise_index']
    ]);

    return response()->json([
        'message' => 'Progress saved successfully',
        'data' => $userWorkout
    ]);
}

    public function complete(Request $request, $id)
    {
        $user = Auth::user();
        $userWorkout = UserWorkout::where('user_id', $user->id)->find($id);

        if (!$userWorkout) {
            return response()->json(['message' => 'Workout not found'], 404);
        }

        $validated = $request->validate([
            'duration_minutes' => 'nullable|integer',
            'calories_burned' => 'nullable|integer',
            'notes' => 'nullable|string',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        $userWorkout->update([
            ...$validated,
            'completed_at' => now(),
            'status' => 'completed',
        ]);

        $this->notifications->checkWorkoutMilestones($user->id);

        return response()->json([
            'message' => 'Workout completed',
            'data' => $userWorkout
        ]);
    }

    public function statistics()
    {
        $user = Auth::user();

        $totalWorkouts = UserWorkout::where('user_id', $user->id)->count();
        $completedWorkouts = UserWorkout::where('user_id', $user->id)->where('status', 'completed')->count();
        $totalCalories = UserWorkout::where('user_id', $user->id)->where('status', 'completed')->sum('calories_burned');
        $totalMinutes = UserWorkout::where('user_id', $user->id)->where('status', 'completed')->sum('duration_minutes');

        return response()->json([
            'total_workouts' => $totalWorkouts,
            'completed_workouts' => $completedWorkouts,
            'total_calories' => $totalCalories,
            'total_minutes' => $totalMinutes,
            'completion_rate' => $totalWorkouts > 0 ? round(($completedWorkouts / $totalWorkouts) * 100) : 0,
        ]);
    }
}
