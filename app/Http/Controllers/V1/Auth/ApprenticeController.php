<?php

namespace App\Http\Controllers\V1\Auth;

use App\Enums\UserType;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Apprentice;
use App\Models\TradeInterest;
use Illuminate\Http\Request;

class ApprenticeController extends Controller
{
    /**
     * POST /api/apprentice_update_details
     * Content-Type: application/json
     */
    public function updateDetails(Request $request)
    {
        $user = auth('api')->user();

        // Only apprentie (user_type = 3) can access this endpoint
        if ($user->user_type !== UserType::APPRENTICE) {
            return ApiResponse::warning('Unauthorized', 403);
        }

        $validated = $request->validate([
            'trade_interest_id'     => 'required|exists:trade_interests,id',
            'trade_school_name'     => 'nullable|string|max:255',
            'current_program_year'  => 'nullable|string|max:255',
            'experience_level'      => 'nullable|string|max:255',
        ]);

        $apprentice = Apprentice::updateOrCreate(
            ['user_id' => $user->id],
            [
                'trade_interest_id'    => $request->input('trade_interest_id'),
                'trade_school_name'    => $request->input('trade_school_name'),
                'current_program_year' => $request->input('current_program_year'),
                'experience_level'     => $request->input('experience_level'),
                'profile_completion'   => true,
            ]
        );

        $tradeInterest = TradeInterest::find($apprentice->trade_interest_id);

        $response = [
            'id'      => $apprentice->id,
            'user_id' => $user->id,
            'role'    => $user->user_type,
            'trade_interest' => $tradeInterest ? [
                'id'   => $tradeInterest->id,
                'name' => $tradeInterest->name,
            ] : null,
            'trade_school' => [
                'name'                => $apprentice->trade_school_name,
                'current_program_year'=> $apprentice->current_program_year,
            ],
            'experience_level'  => $apprentice->experience_level,
            'profile_completion'=> (bool) $apprentice->profile_completion,
            'created_at'        => $apprentice->created_at?->toIso8601String(),
            'updated_at'        => $apprentice->updated_at?->toIso8601String(),
        ];

        return ApiResponse::success('Apprentice details updated successfully', [
            'apprentice' => $response,
        ]);
    }

    /**
     * GET /api/apprentice/trade-interests
     */
    public function tradeInterests()
    {
        $tradeInterests = TradeInterest::select('id', 'name')->get();

        return ApiResponse::success('Trade Interest load successfully', [
            'trade_interests' => $tradeInterests,
        ]);
    }
}
