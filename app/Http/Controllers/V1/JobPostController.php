<?php

namespace App\Http\Controllers\V1;

use App\Enums\UserType;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\JobPost;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class JobPostController extends Controller
{
    /**
     * POST /api/create_job
     */
    public function createJob(Request $request)
    {
        $user = auth('api')->user();

        // Only contractors can create jobs
        if ($user->user_type !== UserType::CONTRACTOR) {
            return ApiResponse::warning('Unauthorized', 403);
        }

        // 1. Validate request with new structure
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'skill_ids' => 'required|array|min:1',
            'skill_ids.*' => 'string',
            'specialization' => 'required|array',
            'specialization.id' => 'required|integer|exists:specializations,id',
            'start_date' => 'required|date',
            'duration' => 'required|array',
            'duration.value' => 'required|integer|min:1',
            'duration.unit' => 'required|string|in:days,weeks,months',
            'pay_rate' => 'required|array',
            'pay_rate.amount' => 'required|numeric|min:0',
            'pay_rate.currency' => 'required|string|size:3',
            'pay_rate.type' => 'required|string|in:hour,day,week,month',
            'location' => 'required|array',
            'location.lat' => 'required|numeric|between:-90,90',
            'location.lng' => 'required|numeric|between:-180,180',
            'location.city' => 'required|string|max:255',
            'job_description' => 'required|string',
            'is_featured' => 'required|boolean',
        ]); // Nested validation ensures all nested objects conform to the expected shape.[web:18][web:190]

        // 2. Map skill codes -> IDs
        $skillCodes = $validated['skill_ids'];

        $skillIds = Skill::whereIn('code', $skillCodes)
            ->pluck('id', 'code');

        if ($skillIds->count() !== count($skillCodes)) {
            return ApiResponse::error('One or more skills are invalid', 422);
        }

        // 3. Generate job code like "JOB12345"
        $jobCode = 'JOB'.mt_rand(10000, 99999);

        $location = $validated['location'];
        $duration = $validated['duration'];
        $payRate = $validated['pay_rate'];

        // 4. Create JobPost with new fields
        $jobPost = JobPost::create([
            'title' => $validated['title'],
            'company_name' => $validated['company_name'],
            'job_code' => $jobCode,
            'user_id' => $user->id,
            'specialization_id' => $validated['specialization']['id'],
            'start_date' => $validated['start_date'],
            'duration_value' => $duration['value'],
            'duration_unit' => $duration['unit'],
            'pay_rate_amount' => $payRate['amount'],
            'pay_rate_currency' => strtoupper($payRate['currency']),
            'pay_rate_type' => $payRate['type'],
            'location_lat' => $location['lat'],
            'location_lng' => $location['lng'],
            'city' => $location['city'],
            'job_description' => $validated['job_description'],
            'is_featured' => $validated['is_featured'],
            'status' => 'pending', // per new response spec
        ]);

        // 5. Attach skills via pivot job_post_skill
        $jobPost->skills()->attach($skillIds->values()->all()); // Standard belongsToMany pivot attach.[web:313][web:121]

        // 6. Response payload
        $data = [
            'job_id' => $jobPost->job_code,
            'job_status' => $jobPost->status,
        ];

        return ApiResponse::success('Job posted successfully', [
            'data' => $data,
        ]);
    }

    /**
     * GET /api/jobs
     *
     * Headers:
     *  - Authorization: Bearer <ACCESS_TOKEN>
     *  - Content-Type: application/json
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listJobs(Request $request)
    {
        // Later you can add filters/pagination; for now fetch all
        $jobs = JobPost::orderByDesc('created_at')->get(); // Simple Eloquent query for listing.[web:29][web:324]

        $data = $jobs->map(function (JobPost $job) {
            return [
                'job_id' => $job->job_code,
                'title' => $job->title ?? 'Carpenter',
                'company_name' => $job->company_name ?? 'ABC Construction',
                'location' => [
                    'city' => $job->city,
                ],
                'is_featured' => (bool) $job->is_featured,
                'pay_rate' => [
                    'amount' => (float) $job->pay_rate_amount,
                    'currency' => $job->pay_rate_currency ?? 'USD',
                    'type' => $job->pay_rate_type ?? 'hour',
                ],
                'duration' => [
                    'value' => (int) $job->duration_value,
                    'unit' => $job->duration_unit,
                ],
                'start_date' => optional($job->start_date)->toDateString(),
                'job_status' => $job->status,
            ];
        })->values()->all(); // map() is the usual way to transform models into custom JSON for APIs.[web:394][web:388]

        return ApiResponse::success(
            'Job list fetched successfully',
            ['data' => $data]
        );
    }

    /**
     * POST /api/edit_job
     *
     * Headers:
     *  - Authorization: Bearer <ACCESS_TOKEN>
     *  - Content-Type: application/json
     *
     * Request:
     * {
     *   "job_id": "JOB12345",
     *   "title": "Carpenter",
     *   "company_name": "ABC Construction",
     *   "skill_ids": ["skill_001", "skill_002"],
     *   "specialization": { "id": 1, "name": "Carpentry" },
     *   "start_date": "2025-01-10",
     *   "duration": { "value": 3, "unit": "days" },
     *   "pay_rate": { "amount": 25, "currency": "USD", "type": "hour" },
     *   "location": { "lat": 37.7749, "lng": -122.4194, "city": "Downtown" },
     *   "job_description": "Updated job description",
     *   "is_featured": true
     * }
     */
    public function editJob(Request $request)
    {
        $user = auth('api')->user();

        // Only contractors can edit jobs
        if ($user->user_type !== UserType::CONTRACTOR) {
            return ApiResponse::warning('Unauthorized', 403);
        }

        try {
            // 1. Validate request payload
            $validated = $request->validate([
                'job_id' => 'required|string',
                'title' => 'required|string|max:255',
                'company_name' => 'required|string|max:255',

                'skill_ids' => 'required|array|min:1',
                'skill_ids.*' => 'string',

                'specialization' => 'required|array',
                'specialization.id' => 'required|integer|exists:specializations,id',

                'start_date' => 'required|date',

                'duration' => 'required|array',
                'duration.value' => 'required|integer|min:1',
                'duration.unit' => 'required|string|in:days,weeks,months',

                'pay_rate' => 'required|array',
                'pay_rate.amount' => 'required|numeric|min:0',
                'pay_rate.currency' => 'required|string|size:3',
                'pay_rate.type' => 'required|string|in:hour,day,week,month',

                'location' => 'required|array',
                'location.lat' => 'required|numeric|between:-90,90',
                'location.lng' => 'required|numeric|between:-180,180',
                'location.city' => 'required|string|max:255',

                'job_description' => 'required|string',
                'is_featured' => 'required|boolean',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation error', 422, $e->errors());
        }

        // 2. Find job by public job_code
        $jobPost = JobPost::where('job_code', $validated['job_id'])->first();

        if (! $jobPost) {
            return ApiResponse::error('Job not found', 404);
        }

        // Optional: ensure the job belongs to the current user
        if ($jobPost->user_id !== $user->id) {
            return ApiResponse::warning('Unauthorized', 403);
        }

        // 3. Map skill codes -> IDs
        $skillCodes = $validated['skill_ids'];

        $skillIds = Skill::whereIn('code', $skillCodes)
            ->pluck('id', 'code');

        if ($skillIds->count() !== count($skillCodes)) {
            return ApiResponse::error('One or more skills are invalid', 422);
        }

        $location = $validated['location'];
        $duration = $validated['duration'];
        $payRate = $validated['pay_rate'];

        // 4. Update job fields
        $jobPost->update([
            'title' => $validated['title'],
            'company_name' => $validated['company_name'],
            'specialization_id' => $validated['specialization']['id'],
            'start_date' => $validated['start_date'],

            'duration_value' => $duration['value'],
            'duration_unit' => $duration['unit'],

            'pay_rate_amount' => $payRate['amount'],
            'pay_rate_currency' => strtoupper($payRate['currency']),
            'pay_rate_type' => $payRate['type'],

            'location_lat' => $location['lat'],
            'location_lng' => $location['lng'],
            'city' => $location['city'],

            'job_description' => $validated['job_description'],
            'is_featured' => $validated['is_featured'],
            // keep existing status as-is (pending/active/etc.)
        ]);

        // 5. Sync skills (replace old with new)
        $jobPost->skills()->sync($skillIds->values()->all());

        // 6. Response
        $data = [
            'job_id' => $jobPost->job_code,
        ];

        return ApiResponse::success('Job edited successfully', [
            'data' => $data,
        ]);
    }

    /**
     * POST /api/delete_job
     *
     * Headers:
     *  - Authorization: Bearer <ACCESS_TOKEN>
     *  - Content-Type: application/json
     *
     * Body:
     * {
     *   "job_id": "JOB12345"
     * }
     */
    public function deleteJob(Request $request)
    {
        $user = auth('api')->user();

        // Only contractors can delete jobs
        if ($user->user_type !== UserType::CONTRACTOR) {
            return ApiResponse::warning('Unauthorized', 403);
        }

        try {
            $validated = $request->validate([
                'job_id' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation error', 422, $e->errors());
        }

        // Find job by public code
        $jobPost = JobPost::where('job_code', $validated['job_id'])->first();

        if (! $jobPost) {
            return ApiResponse::warning('Job not found', 404);
        }

        // Ensure the job belongs to current user
        if ($jobPost->user_id !== $user->id) {
            return ApiResponse::warning('Unauthorized', 403);
        }

        // Detach related skills (pivot) then delete job
        $jobPost->skills()->detach();
        $jobPost->delete();

        return ApiResponse::success('Job deleted successfully', [
            'data' => [
                'job_id' => $validated['job_id'],
            ],
        ]);
    }

    /**
     * POST /api/jobs/update-status
     *
     * Body:
     * {
     *   "job_id": "JOB12345",
     *   "status": "completed"
     * }
     */
    public function updateJobStatus(Request $request)
    {
        $user = auth('api')->user();

        // Only contractors can change job status (adjust if needed)
        if ($user->user_type !== UserType::CONTRACTOR) {
            return ApiResponse::warning('Unauthorized', 403);
        }

        try {
            $validated = $request->validate([
                'job_id' => 'required|string',
                // Limit to allowed statuses
                'status' => 'required|string|in:pending,active,completed,cancelled',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation error', 422, $e->errors());
        }

        // Find job by public code
        $jobPost = JobPost::where('job_code', $validated['job_id'])->first();

        if (! $jobPost) {
            return ApiResponse::warning('Job not found', 404);
        }

        // Ensure the job belongs to current user
        if ($jobPost->user_id !== $user->id) {
            return ApiResponse::warning('Unauthorized', 403);
        }

        // Update status
        $jobPost->status = $validated['status'];
        $jobPost->save();

        return ApiResponse::success('Job status updated successfully', [
            'data' => [
                'job_id' => $jobPost->job_code,
                'job_status' => $jobPost->status,
            ],
        ]);
    }

}
