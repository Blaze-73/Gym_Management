<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlanRequest;
use App\Models\Plan;
use App\Services\PlanEntitlementService;
use App\Support\PlanCatalog;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(PlanEntitlementService $entitlements)
    {
        $plans = Plan::whereIn('id', PlanCatalog::membershipPlanIds())
            ->orderBy('id')
            ->get()
            ->map(fn (Plan $plan) => $entitlements->formatPlanForApi($plan))
            ->values();

        return response()->json($plans);
    }
    /**
     * Store a newly created resource in storage.
     */
  public function store(StorePlanRequest $request)
{
    $plan = Plan::create($request->validated());
    return response()->json([
        'message' => 'Plan created successfully',
        'data' => $plan
    ], 201);
}

    /**
     * Display the specified resource.
     */
    public function show( $id)
    {
        $plan = Plan::find($id);
        if(!$plan){
            return response()->json(['message'=>'plan not found']);
        }
        return response()->json($plan);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StorePlanRequest $request, string $id)
    {
        $plan = Plan::find($id);
        if(!$plan){
            return response()->json(['message'=>'plan not found']);
        }
        $plan->update($request->validate());
        return response()->json([
            'message' => 'plan update succesfully',
            'data'=> $plan
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $plan = Plan::find($id);
        if(!$plan){
            return response()->json(['message'=>'plan not found']);
        }
        $plan->delete();
        return response()->json(['message'=>'Plan deleted succesfully']);
    }
}
