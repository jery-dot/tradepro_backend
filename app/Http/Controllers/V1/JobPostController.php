<?php

namespace App\Http\Controllers\V1;

use App\Enums\UserType;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\JobPost;
use App\Models\Skill;
use Illuminate\Http\Request;

class JobPostController extends Controller
{
    /**
     * POST /api/create_job
     */
    public function createJob(Request $request)
    {
        $user = auth('api')->user();

        // Only contractors can create job posts (optional rule)
        if ($user->user_type !== UserType::CONTRACTOR) {
            return ApiResponse::warning('Unauthorized', 403);
        }

        $validated = $request->validate([
            'skill_ids' => 'required|array|min:1',
            'skill_ids.*' => 'string',
            'specialization' => 'required|array',
            'specialization.id' => 'required|integer|exists:specializations,id',
            'start_date' => 'required|date',
            'duration' => 'required|array',
            'duration.value' => 'required|integer|min:1',
            'duration.unit' => 'required|string|in:days,weeks,months',
            'pay_range' => 'required|numeric|min:0',
            'location' => 'required|array',
            'location.lat' => 'required|numeric|between:-90,90',
            'location.lng' => 'required|numeric|between:-180,180',
            'job_description' => 'required|string',
            'is_featured' => 'required|boolean',
        ]);

        // Map skill codes -> IDs
        $skillCodes = $validated['skill_ids'];

        $skillIds = Skill::whereIn('code', $skillCodes)
            ->pluck('id', 'code');

        if ($skillIds->count() !== count($skillCodes)) {
            return ApiResponse::error('One or more skills are invalid', 422);
        }

        // Generate unique job code
        $jobCode = 'job_'.mt_rand(100000, 999999);

        $location = $validated['location'];
        $duration = $validated['duration'];

        // Create JobPost
        $jobPost = JobPost::create([
            'job_code' => $jobCode,
            'user_id' => $user->id,
            'specialization_id' => $validated['specialization']['id'],
            'start_date' => $validated['start_date'],
            'duration_value' => $duration['value'],
            'duration_unit' => $duration['unit'],
            'pay_range' => $validated['pay_range'],
            'location_lat' => $location['lat'],
            'location_lng' => $location['lng'],
            'job_description' => $validated['job_description'],
            'is_featured' => $validated['is_featured'],
            'status' => 'active',
        ]);

        // Attach skills via pivot job_post_skill
        $jobPost->skills()->attach($skillIds->values()->all()); // Standard belongsToMany attach for pivot tables.[web:313][web:121]

        $data = [
            'job_id' => $jobPost->job_code,
            'status' => $jobPost->status,
        ];

        return ApiResponse::success('Job posted successfully', [
            'data' => $data,
        ]);
    }
}
