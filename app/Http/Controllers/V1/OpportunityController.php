<?php

namespace App\Http\Controllers\V1;

use App\Enums\UserType;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Opportunity;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    /**
     * POST /api/post_opportunity
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function postOpportunity(Request $request)
    {
        $user = auth('api')->user();

        // Only contractors and subcontractors can post opportunities
        if (($user->user_type !== UserType::CONTRACTOR) && ($user->user_type !== UserType::SUBCONTRACTOR)) {
            return ApiResponse::warning('Unauthorized', 403);
        }

        $validated = $request->validate([
            'skills_needed' => 'required|array|min:1',
            'skills_needed.*' => 'string|max:255',
            'apprenticeship_start_date' => 'required|date',
            'duration_weeks' => 'required|integer|min:1',
            'compensation_paid' => 'required|boolean',
            'total_pay_offering' => 'nullable|numeric|min:0|required_if:compensation_paid,true',
            'location' => 'required|array',
            'location.lat' => 'required|numeric|between:-90,90',
            'location.lng' => 'required|numeric|between:-180,180',
            'location.city' => 'required|string|max:255',
            'apprenticeship_description' => 'required|string',
        ]); // required_if is the idiomatic way to conditionally require a field.[web:3][web:66][web:72]

        $skills = $validated['skills_needed'];
        $location = $validated['location'];

        $opportunity = Opportunity::create([
            'public_id' => 'opportunity_'.str_pad((string) (Opportunity::max('id') + 1), 3, '0', STR_PAD_LEFT),
            'apprenticeship_id' => 'APP-'.random_int(10000, 99999),
            'user_id' => $user->id,
            'skills_needed' => $skills,
            'apprenticeship_start_date' => $validated['apprenticeship_start_date'],
            'duration_weeks' => $validated['duration_weeks'],
            'compensation_paid' => $validated['compensation_paid'],
            'total_pay_offering' => $validated['compensation_paid'] ? $validated['total_pay_offering'] : null,
            'lat' => $location['lat'],
            'lng' => $location['lng'],
            'city' => $location['city'],
            'title' => $skills[0] ?? null, // optional: first skill as title
            'apprenticeship_description' => $validated['apprenticeship_description'],
        ]);

        $responseData = [
            'status' => 'success',
            'message' => 'Apprenticeship form submitted successfully.',
            'apprenticeship_id' => $opportunity->apprenticeship_id,
            'submitted_at' => now()->toIso8601String(),
            'data' => [
                'skills_needed' => $opportunity->skills_needed,
                'apprenticeship_start_date' => $opportunity->apprenticeship_start_date->toDateString(),
                'duration_weeks' => $opportunity->duration_weeks,
                'compensation_paid' => $opportunity->compensation_paid,
                'total_pay_offering' => $opportunity->total_pay_offering,
                'location' => [
                    'lat' => (float) $opportunity->lat,
                    'lng' => (float) $opportunity->lng,
                    'city' => $opportunity->city,
                ],
                'apprenticeship_description' => $opportunity->apprenticeship_description,
            ],
        ];

        return response()->json($responseData, 201);
    }

    /**
     * GET /api/get_opportunities?page=&limit=
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOpportunities(Request $request)
    {
        $user = auth('api')->user();
        $page = (int) $request->query('page', 1);
        $limit = (int) $request->query('limit', 10);
        $limit = $limit > 0 ? min($limit, 100) : 10; // typical page/limit handling.[web:65][web:73]

        $paginator = Opportunity::with('user')
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        $items = $paginator->getCollection()->map(function (Opportunity $opportunity) use ($user) {
            $owner = $opportunity->user;

            $canEditDelete = $owner && $owner->id === $user->id;

            return [
                'id' => $opportunity->id,
                'apprenticeship_id' => $opportunity->apprenticeship_id,
                'title' => $opportunity->title ?? ($opportunity->skills_needed[0] ?? null),
                'posted_by' => $owner?->name,
                'location' => [
                    'lat' => (float) $opportunity->lat,
                    'lng' => (float) $opportunity->lng,
                    'city' => $opportunity->city,
                ],
                'compensation_paid' => $opportunity->compensation_paid,
                'total_pay_offering' => $opportunity->total_pay_offering,
                'duration_weeks' => $opportunity->duration_weeks,
                'apprenticeship_start_date' => optional($opportunity->apprenticeship_start_date)->toDateString(),
                'skills_needed' => $opportunity->skills_needed ?? [],
                'apprenticeship_description' => $opportunity->apprenticeship_description,
                'created_at' => $opportunity->created_at?->toIso8601String(),
                'edit_available' => $canEditDelete,
                'delete_available' => $canEditDelete,
            ];
        })->values()->all(); // map() for transforming Eloquent collections into API resources.

        return response()->json([
            'status' => 'success',
            'message' => 'Opportunities fetched successfully.',
            'count' => count($items),
            'data' => $items,
        ]);
    }

    /**
     * POST /api/edit_opportunity
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function editOpportunity(Request $request)
    {
        $user = auth('api')->user();

        $validated = $request->validate([
            'id' => 'required|string',
            'skills_needed' => 'nullable|array|min:1',
            'skills_needed.*' => 'string|max:255',
            'apprenticeship_start_date' => 'nullable|date',
            'duration_weeks' => 'nullable|integer|min:1',
            'compensation_paid' => 'nullable|boolean',
            'total_pay_offering' => 'nullable|numeric|min:0',
            'location' => 'nullable|array',
            'location.lat' => 'nullable|numeric|between:-90,90',
            'location.lng' => 'nullable|numeric|between:-180,180',
            'location.city' => 'nullable|string|max:255',
            'apprenticeship_description' => 'nullable|string',
        ]);

        $opportunity = Opportunity::where('public_id', $validated['id'])->first();

        if (! $opportunity) {
            return response()->json([
                'status' => 'error',
                'message' => 'Opportunity not found.',
            ], 404);
        }

        if ($opportunity->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ], 403);
        }

        // Apply updates conditionally
        if (array_key_exists('skills_needed', $validated)) {
            $opportunity->skills_needed = $validated['skills_needed'];
            $opportunity->title = $validated['skills_needed'][0] ?? $opportunity->title;
        }

        if (array_key_exists('apprenticeship_start_date', $validated)) {
            $opportunity->apprenticeship_start_date = $validated['apprenticeship_start_date'];
        }

        if (array_key_exists('duration_weeks', $validated)) {
            $opportunity->duration_weeks = $validated['duration_weeks'];
        }

        if (array_key_exists('compensation_paid', $validated)) {
            $opportunity->compensation_paid = $validated['compensation_paid'];

            // enforce conditional requirement manually on update
            if ($validated['compensation_paid'] && empty($validated['total_pay_offering'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'total_pay_offering is required when compensation_paid is true.',
                ], 422);
            }
        }

        if (array_key_exists('total_pay_offering', $validated)) {
            $opportunity->total_pay_offering = $validated['total_pay_offering'];
        }

        if (array_key_exists('location', $validated)) {
            $loc = $validated['location'];
            if (array_key_exists('lat', $loc)) {
                $opportunity->lat = $loc['lat'];
            }
            if (array_key_exists('lng', $loc)) {
                $opportunity->lng = $loc['lng'];
            }
            if (array_key_exists('city', $loc)) {
                $opportunity->city = $loc['city'];
            }
        }

        if (array_key_exists('apprenticeship_description', $validated)) {
            $opportunity->apprenticeship_description = $validated['apprenticeship_description'];
        }

        $opportunity->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Apprenticeship form updated successfully.',
            'id' => $opportunity->id,
            'apprenticeship_id' => $opportunity->apprenticeship_id,
            'updated_at' => now()->toIso8601String(),
            'data' => [
                'skills_needed' => $opportunity->skills_needed,
                'apprenticeship_start_date' => optional($opportunity->apprenticeship_start_date)->toDateString(),
                'duration_weeks' => $opportunity->duration_weeks,
                'compensation_paid' => $opportunity->compensation_paid,
                'total_pay_offering' => $opportunity->total_pay_offering,
                'location' => [
                    'lat' => (float) $opportunity->lat,
                    'lng' => (float) $opportunity->lng,
                    'city' => $opportunity->city,
                ],
                'apprenticeship_description' => $opportunity->apprenticeship_description,
            ],
        ]);
    }


    /**
     * POST /api/delete_opportunity
     */
    public function deleteOpportunity(Request $request)
{
    $user = auth('api')->user();

    $validated = $request->validate([
        'id' => 'required|string',
    ]);

    $opportunity = Opportunity::where('public_id', $validated['id'])->first();

    if (! $opportunity) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Opportunity not found.',
        ], 404);
    }

    if ($opportunity->user_id !== $user->id) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Unauthorized.',
        ], 403);
    }

    $opportunity->delete(); // sets deleted_at thanks to SoftDeletes.[web:13][web:68]

    return response()->json([
        'status'     => 'success',
        'message'    => 'Apprenticeship opportunity deleted successfully.',
        'id'         => $opportunity->id,
        'deleted_at' => now()->toIso8601String(),
    ]);
}

}
