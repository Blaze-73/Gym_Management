<?php

namespace App\Http\Controllers;

use App\Services\PlanEntitlementService;
use Illuminate\Http\Request;

class EntitlementController extends Controller
{
    public function me(Request $request, PlanEntitlementService $entitlements)
    {
        $bundle = $entitlements->bundleForUser($request->user());
        $pricing = $entitlements->applyStoreDiscount(100, $request->user());
        $bundle['store_discount_preview'] = [
            'percent' => $pricing['discount_percent'],
            'example_subtotal' => 100,
            'example_discount' => $pricing['discount_amount'],
            'example_total' => $pricing['total'],
        ];

        return response()->json($bundle);
    }
}
