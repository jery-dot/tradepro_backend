<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * GET available subscription plans
     */
    public function getPlans()
    {   
        $plans = Plan::select(['id', 'name', 'price', 'currency', 'billing_cycle', 'trial_days', 'features'])
                    ->get();

        return response()->json([
            'status' => 'success',
            'data' => $plans
        ]);
    }

}
