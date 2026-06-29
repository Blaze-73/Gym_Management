<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use App\Models\NutritionLog;
use App\Services\PlanEntitlementService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NutritionController extends Controller
{
    public function __construct(
        protected PlanEntitlementService $entitlements
    ) {}

    private function nutritionDenied()
    {
        return response()->json(
            $this->entitlements->deniedResponse(
                'nutrition_access',
                'Nutrition is included with Alpha Orbit and Interstellar plans.'
            ),
            403
        );
    }

    private function nutritionDeniedIfNeeded($user)
    {
        if (!$this->entitlements->hasEntitlement($user, 'nutrition_access')) {
            return $this->nutritionDenied();
        }

        return null;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if ($denied = $this->nutritionDeniedIfNeeded($user)) {
            return $denied;
        }
        $days = min(max((int) $request->input('days', 30), 1), 90);
        $startDate = now()->subDays($days - 1)->startOfDay();

        $logs = NutritionLog::with(['meals' => fn ($q) => $q->orderBy('created_at')])
            ->where('user_id', $user->id)
            ->where('log_date', '>=', $startDate)
            ->orderByDesc('log_date')
            ->get();

        return response()->json($logs);
    }

    public function history(Request $request)
    {
        $user = Auth::user();
        if ($denied = $this->nutritionDeniedIfNeeded($user)) {
            return $denied;
        }
        $days = min(max((int) $request->input('days', 14), 1), 90);
        $end = now()->startOfDay();
        $start = $end->copy()->subDays($days - 1);

        $logsByDate = NutritionLog::withCount('meals')
            ->where('user_id', $user->id)
            ->whereBetween('log_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn ($log) => $log->log_date->format('Y-m-d'));

        $calendar = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $key = $d->format('Y-m-d');
            $log = $logsByDate->get($key);
            $calendar[] = [
                'date' => $key,
                'label' => $d->format('D'),
                'day' => $d->format('j'),
                'is_today' => $d->isToday(),
                'has_meals' => $log && $log->meals_count > 0,
                'calories' => $log?->calories ?? 0,
                'meal_count' => $log?->meals_count ?? 0,
                'water_ml' => $log?->water_ml ?? 0,
            ];
        }

        return response()->json([
            'days' => array_reverse($calendar),
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
        ]);
    }

    public function show($date)
    {
        $user = Auth::user();
        if ($denied = $this->nutritionDeniedIfNeeded($user)) {
            return $denied;
        }

        try {
            $parsed = Carbon::parse($date)->toDateString();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid date.'], 422);
        }

        $log = NutritionLog::firstOrCreate(
            ['user_id' => $user->id, 'log_date' => $parsed],
            $this->defaultTargets()
        );

        $meals = Meal::where('user_id', $user->id)
            ->where('nutrition_log_id', $log->id)
            ->orderByRaw('COALESCE(eaten_at, created_at) ASC')
            ->orderBy('created_at')
            ->get();

        $this->syncLogTotals($log);

        return response()->json($this->formatDayResponse($log, $meals));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($denied = $this->nutritionDeniedIfNeeded($user)) {
            return $denied;
        }

        $validated = $request->validate([
            'log_date' => 'required|date',
            'water_ml' => 'nullable|integer|min:0',
            'target_calories' => 'nullable|integer|min:0',
            'target_protein_g' => 'nullable|integer|min:0',
            'target_carbs_g' => 'nullable|integer|min:0',
            'target_fats_g' => 'nullable|integer|min:0',
            'target_water_ml' => 'nullable|integer|min:0',
        ]);

        $log = NutritionLog::firstOrCreate(
            ['user_id' => $user->id, 'log_date' => $validated['log_date']],
            $this->defaultTargets()
        );

        $updates = collect($validated)->only([
            'water_ml', 'target_calories', 'target_protein_g', 'target_carbs_g', 'target_fats_g', 'target_water_ml',
        ])->filter(fn ($v) => $v !== null)->all();

        if ($updates !== []) {
            $log->update($updates);
        }

        $meals = Meal::where('nutrition_log_id', $log->id)->orderBy('created_at')->get();

        return response()->json([
            'message' => 'Nutrition log saved successfully',
            'data' => $this->formatDayResponse($log, $meals),
        ]);
    }

    public function addMeal(Request $request)
    {
        $user = Auth::user();
        if ($denied = $this->nutritionDeniedIfNeeded($user)) {
            return $denied;
        }

        $validated = $request->validate([
            'log_date' => 'required|date',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'meal_type' => 'required|in:breakfast,lunch,dinner,snack',
            'calories' => 'required|integer|min:0',
            'protein_g' => 'nullable|numeric|min:0',
            'carbs_g' => 'nullable|numeric|min:0',
            'fats_g' => 'nullable|numeric|min:0',
            'eaten_at' => 'nullable|date_format:H:i',
        ]);

        $log = NutritionLog::firstOrCreate(
            ['user_id' => $user->id, 'log_date' => $validated['log_date']],
            $this->defaultTargets()
        );

        $meal = Meal::create([
            'nutrition_log_id' => $log->id,
            'user_id' => $user->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'meal_type' => $validated['meal_type'],
            'calories' => (int) $validated['calories'],
            'protein_g' => (int) round($validated['protein_g'] ?? 0),
            'carbs_g' => (int) round($validated['carbs_g'] ?? 0),
            'fats_g' => (int) round($validated['fats_g'] ?? 0),
            'eaten_at' => isset($validated['eaten_at']) ? $validated['eaten_at'] : now()->format('H:i:s'),
        ]);

        $this->syncLogTotals($log);

        return response()->json([
            'message' => 'Meal added successfully',
            'data' => $meal,
            'day' => $this->formatDayResponse($log->fresh(), $log->meals()->orderBy('created_at')->get()),
        ], 201);
    }

    public function updateMeal(Request $request, $id)
    {
        $user = Auth::user();
        if ($denied = $this->nutritionDeniedIfNeeded($user)) {
            return $denied;
        }
        $meal = Meal::where('user_id', $user->id)->find($id);

        if (!$meal) {
            return response()->json(['message' => 'Meal not found'], 404);
        }

        $meal->update($request->validate([
            'name' => 'sometimes|required|string|max:255',
            'meal_type' => 'sometimes|required|in:breakfast,lunch,dinner,snack',
            'calories' => 'sometimes|required|integer|min:0',
            'protein_g' => 'nullable|numeric|min:0',
            'carbs_g' => 'nullable|numeric|min:0',
            'fats_g' => 'nullable|numeric|min:0',
        ]));

        if ($meal->nutrition_log_id) {
            $this->syncLogTotals(NutritionLog::find($meal->nutrition_log_id));
        }

        return response()->json([
            'message' => 'Meal updated successfully',
            'data' => $meal->fresh(),
        ]);
    }

    public function deleteMeal($id)
    {
        $user = Auth::user();
        if ($denied = $this->nutritionDeniedIfNeeded($user)) {
            return $denied;
        }
        $meal = Meal::where('user_id', $user->id)->find($id);

        if (!$meal) {
            return response()->json(['message' => 'Meal not found'], 404);
        }

        $logId = $meal->nutrition_log_id;
        $meal->delete();

        if ($logId) {
            $log = NutritionLog::find($logId);
            if ($log) {
                $this->syncLogTotals($log);
            }
        }

        return response()->json(['message' => 'Meal deleted successfully']);
    }

    protected function defaultTargets(): array
    {
        return [
            'calories' => 0,
            'protein_g' => 0,
            'carbs_g' => 0,
            'fats_g' => 0,
            'water_ml' => 0,
            'target_calories' => 2500,
            'target_protein_g' => 180,
            'target_carbs_g' => 300,
            'target_fats_g' => 80,
            'target_water_ml' => 3000,
        ];
    }

    protected function syncLogTotals(NutritionLog $log): void
    {
        $totals = Meal::where('nutrition_log_id', $log->id)
            ->selectRaw('COALESCE(SUM(calories),0) as calories, COALESCE(SUM(protein_g),0) as protein_g, COALESCE(SUM(carbs_g),0) as carbs_g, COALESCE(SUM(fats_g),0) as fats_g')
            ->first();

        $log->update([
            'calories' => (int) $totals->calories,
            'protein_g' => (int) $totals->protein_g,
            'carbs_g' => (int) $totals->carbs_g,
            'fats_g' => (int) $totals->fats_g,
        ]);
    }

    protected function formatDayResponse(NutritionLog $log, $meals): array
    {
        $grouped = [
            'breakfast' => [],
            'lunch' => [],
            'dinner' => [],
            'snack' => [],
        ];

        foreach ($meals as $meal) {
            $type = in_array($meal->meal_type, array_keys($grouped)) ? $meal->meal_type : 'snack';
            $grouped[$type][] = $meal;
        }

        return [
            'log_date' => $log->log_date->format('Y-m-d'),
            'meals' => $meals,
            'meals_by_type' => $grouped,
            'meal_count' => $meals->count(),
            'calories' => (int) $log->calories,
            'protein_g' => (int) $log->protein_g,
            'carbs_g' => (int) $log->carbs_g,
            'fats_g' => (int) $log->fats_g,
            'water_ml' => (int) $log->water_ml,
            'target_calories' => (int) $log->target_calories,
            'target_protein_g' => (int) $log->target_protein_g,
            'target_carbs_g' => (int) $log->target_carbs_g,
            'target_fats_g' => (int) $log->target_fats_g,
            'target_water_ml' => (int) $log->target_water_ml,
        ];
    }
}
