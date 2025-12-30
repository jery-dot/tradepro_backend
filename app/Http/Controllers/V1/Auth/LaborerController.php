<?php

namespace App\Http\Controllers\V1\Auth;

use App\Enums\UserType;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Laborer;
use App\Models\Specialization;
use Illuminate\Http\Request;

class LaborerController extends Controller
{
    /**
     * POST /api/laborer_update_details
     * Header: Authorization: Bearer <token>
     */
    public function updateDetails(Request $request)
    {
        $user = auth('api')->user();

        // Only laborer (user_type = 2) can access this endpoint
        if ($user->user_type !== UserType::LABORER) {
            return ApiResponse::warning('Unauthorized', 403);
        }

        $validated = $request->validate([
            'specialization_id'        => 'nullable|exists:specializations,id',
            'custom_specialization'    => 'nullable|string|max:255',
            'experience_level'         => 'nullable|string|max:255',
            'age'                      => 'nullable|integer|min:18|max:100',
            'gender'                   => 'nullable|in:male,female,other',
            'has_insurance'            => 'required|boolean',
            'background_check_completed' => 'required|boolean',
            'looking_for_apprenticeship' => 'nullable|boolean',
            'trade_school.name'        => 'nullable|string|max:255',
            'trade_school.program_year'=> 'nullable|string|max:255',
        ]);

        // Create or update laborer profile for this user
        $laborer = Laborer::updateOrCreate(
            ['user_id' => $user->id],
            [
                'specialization_id'         => $request->input('specialization_id'),
                'custom_specialization'     => $request->input('custom_specialization'),
                'experience_level'          => $request->input('experience_level'),
                'age'                       => $request->input('age'),
                'gender'                    => $request->input('gender'),
                'has_insurance'             => $request->boolean('has_insurance'),
                'background_check_completed'=> $request->boolean('background_check_completed'),
                'looking_for_apprenticeship'=> $request->boolean('looking_for_apprenticeship', false),
                'trade_school_name'         => $request->input('trade_school.name'),
                'trade_school_program_year' => $request->input('trade_school.program_year'),
                'profile_completion'        => true,
            ]
        );

        // Build specialization object
        $specialization = null;
        if ($laborer->specialization_id) {
            $spec = Specialization::find($laborer->specialization_id);
            if ($spec) {
                $specialization = [
                    'id'   => $spec->id,
                    'name' => $spec->name,
                ];
            }
        }

        $responseLaborer = [
            'id'                          => $laborer->id,
            'user_id'                     => $user->id,
            'email'                       => $user->email,
            'role'                        => $user->user_type,
            'specialization'              => $specialization,
            'custom_specialization'       => $laborer->custom_specialization,
            'experience_level'            => $laborer->experience_level,
            'age'                         => $laborer->age,
            'gender'                      => $laborer->gender,
            'has_insurance'               => (bool) $laborer->has_insurance,
            'background_check_completed'  => (bool) $laborer->background_check_completed,
            'looking_for_apprenticeship'  => (bool) $laborer->looking_for_apprenticeship,
            'trade_school' => [
                'name'         => $laborer->trade_school_name,
                'program_year' => $laborer->trade_school_program_year,
            ],
            'profile_completion' => (bool) $laborer->profile_completion,
            'created_at'         => $laborer->created_at?->toIso8601String(),
            'updated_at'         => $laborer->updated_at?->toIso8601String(),
        ];

        return ApiResponse::success('Laborer details updated successfully', [
            'laborer' => $responseLaborer,
        ]);
    }

    /**
     * GET /api/laborer/specializations
     */
    public function listSpecializations()
    {
        $specializations = Specialization::select('id', 'name')->get();

        return ApiResponse::success('Specializations load successfully', [
            'specializations' => $specializations,
        ]);
    }
}
