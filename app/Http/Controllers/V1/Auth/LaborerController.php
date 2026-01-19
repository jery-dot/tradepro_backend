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
            'specialization_id' => 'nullable|exists:specializations,id',
            'custom_specialization' => 'nullable|string|max:255',
            'experience_level' => 'nullable|string|max:255',
            'age' => 'nullable|integer|min:18|max:100',
            'gender' => 'nullable|in:male,female,other',
            'has_insurance' => 'required|boolean',
            'background_check_completed' => 'required|boolean',
            'looking_for_apprenticeship' => 'nullable|boolean',
            'trade_school.name' => 'nullable|string|max:255',
            'trade_school.program_year' => 'nullable|string|max:255',
        ]);

        // Create or update laborer profile for this user
        $laborer = Laborer::updateOrCreate(
            ['user_id' => $user->id],
            [
                'specialization_id' => $request->input('specialization_id'),
                'custom_specialization' => $request->input('custom_specialization'),
                'experience_level' => $request->input('experience_level'),
                'age' => $request->input('age'),
                'gender' => $request->input('gender'),
                'has_insurance' => $request->boolean('has_insurance'),
                'background_check_completed' => $request->boolean('background_check_completed'),
                'looking_for_apprenticeship' => $request->boolean('looking_for_apprenticeship', false),
                'trade_school_name' => $request->input('trade_school.name'),
                'trade_school_program_year' => $request->input('trade_school.program_year'),
                'profile_completion' => true,
            ]
        );

        // Build specialization object
        $specialization = null;
        if ($laborer->specialization_id) {
            $spec = Specialization::find($laborer->specialization_id);
            if ($spec) {
                $specialization = [
                    'id' => $spec->id,
                    'name' => $spec->name,
                ];
            }
        }

        $responseLaborer = [
            'id' => $laborer->id,
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->user_type->label() ?? '--',
            // 'specialization'              => $specialization,
            'custom_specialization' => $laborer->custom_specialization,
            'experience_level' => $laborer->experience_level,
            'age' => $laborer->age,
            'gender' => $laborer->gender,
            'has_insurance' => (bool) $laborer->has_insurance,
            'background_check_completed' => (bool) $laborer->background_check_completed,
            'looking_for_apprenticeship' => (bool) $laborer->looking_for_apprenticeship,
            'trade_school' => [
                'name' => $laborer->trade_school_name,
                'program_year' => $laborer->trade_school_program_year,
            ],
            'profile_completion' => (bool) $laborer->profile_completion,
            'created_at' => $laborer->created_at?->toIso8601String(),
            'updated_at' => $laborer->updated_at?->toIso8601String(),
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

    /**
     *  GET /api/get_labor_profile
     */
    public function getLaborProfile(Request $request)
    {
        $user = auth('api')->user();

        // Only laborer (user_type = 2) can access this endpoint
        if ($user->user_type !== UserType::LABORER) {
            return ApiResponse::warning('Unauthorized', 403);
        }

        // Rating can come from receivedReviews relationship
        $rating = round($user->receivedReviews()->avg('overall_rating') ?? 0, 1);

        return response()->json([
            'status' => 'success',
            'message' => 'Laboer profile fetched successfully.',
            'data' => [
                'id' => $user->id,
                'full_name' => $user->name,
                'role' => $user->role_label ?? 'Labor',
                'status' => $user->status ?? null,
                'rating' => $rating,
                'profile_image_url' => $user->profile_image,
                'location' => $user->location_text,
                'email' => $user->email,
                'insurance' => (bool) $user->laborer->has_insurance,
                'background_check_completed' => (bool) $user->laborer->background_check_completed,
                'looking_for_apprenticeship' => (bool) $user->laborer->looking_for_apprenticeship,
                'created_at' => $user->created_at?->toIso8601String(),
                'updated_at' => $user->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Summary of updateInsuranceStatus
     *
     * POST /api/update_user_insurance_status
     *
     * Request Body:
     *  - "id": "user_001",
     *  - "insurance": boolean
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateInsuranceStatus(Request $request)
    {
        $user = auth('api')->user();

        $validated = $request->validate([
            'insurance' => 'required|boolean',
        ]);

        $user->laborer->has_insurance = $validated['insurance'];
        $user->laborer->save();

        return response()->json([
            'status' => 'success',
            'message' => 'User insurance status updated successfully.',
            'id' => 'user_'.$user->id,
            'updated_at' => $user->updated_at?->toIso8601String(),
            'data' => [
                'insurance' =>  $user->laborer->has_insurance ,
            ],
        ]);
    }

    /**
     * POST /api/update_user_background_check_status
     *
     * Request Body:
     *  - "id": "user_001",
     *  - "background_check_completed": boolean
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateBackgroundCheckStatus(Request $request)
    {
        $user = auth('api')->user();

        $validated = $request->validate([
            'background_check_completed' => 'required|boolean',
        ]);

        $user->laborer->background_check_completed = $validated['background_check_completed'];
        $user->laborer->save();

        return response()->json([
            'status' => 'success',
            'message' => 'User background check status updated successfully.',
            'id' => 'user_'.$user->id,
            'updated_at' => $user->updated_at?->toIso8601String(),
            'data' => [
                'background_check_completed' => $user->laborer->background_check_completed,
            ],
        ]);
    }

    /**
     * Summary of updateApprenticeshipStatus
     *
     * POST /api/update_user_apprenticeship_status
     *
     * - "id": "user_001"
     * - "looking_for_apprenticeship": true

     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateApprenticeshipStatus(Request $request)
    {
        $user = auth('api')->user();

        $validated = $request->validate([
            'looking_for_apprenticeship' => 'required|boolean',
        ]);

        $user->laborer->looking_for_apprenticeship = $validated['looking_for_apprenticeship'];
        $user->laborer->save();

        return response()->json([
            'status' => 'success',
            'message' => 'User apprenticeship status updated successfully.',
            'id' => 'user_'.$user->id,
            'updated_at' => $user->updated_at?->toIso8601String(),
            'data' => [
                'looking_for_apprenticeship' => $user->laborer->looking_for_apprenticeship,
            ],
        ]);
    }
}
