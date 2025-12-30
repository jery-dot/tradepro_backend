<?php

namespace App\Http\Controllers\V1\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Contractor;
use App\Models\JobRequirement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\FileUploadHelper;
use App\Enums\UserType;

class ContractorController extends Controller
{
    /**
     * Update contractor profile details.
     *
     * Endpoint:
     * POST /api/contractor_update_details
     * Headers:
     *   Authorization: Bearer <ACCESS_TOKEN>
     *   Content-Type: multipart/form-data
     *
     * Example form-data:
     *   latitude: 28.6139
     *   longitude: 77.2090
     *   insurance_file: test.pdf
     *   job_requirements[]: background_check_requirement
     *   job_requirements[]: insurance_required
     *   job_requirements[]: safety_certification
     */
    public function updateDetails(Request $request)
    {
        // ---------------------------------------------------------------------
        // 1. Authenticate and authorize
        // ---------------------------------------------------------------------
        $user = auth('api')->user();

        // Only contractors (user_type = 0) can access this endpoint
        if ($user->user_type !== UserType::CONTRACTOR) {
            return ApiResponse::warning('Unauthorized', 403);
        }

        // ---------------------------------------------------------------------
        // 2. Validate incoming request
        // ---------------------------------------------------------------------
        $validated = $request->validate([
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'insurance_file' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            'job_requirements' => 'nullable|array',
            'job_requirements.*' => 'string', // slugs like "background_check_requirement"
        ]);

        // ---------------------------------------------------------------------
        // 3. Get or create contractor profile for this user
        // ---------------------------------------------------------------------
        $contractor = Contractor::firstOrCreate(
            ['user_id' => $user->id],
            [] // default attributes when creating new record
        );


        // ---------------------------------------------------------------------
        // 4. Handle insurance file upload (using helper)
        // ---------------------------------------------------------------------
        if ($request->hasFile('insurance_file')) {

            // Old file (if any) to be deleted after successful upload
            $oldFile = $contractor->file_path
                ? basename($contractor->file_path)   // only filename, without directory
                : null;

            // Upload new file to public/insurance via helper
            $filename = FileUploadHelper::upload(
                $request->file('insurance_file'),
                'insurance',   // directory relative to public/
                $oldFile,
                'contractor_'.$contractor->id
            );

            // Persist relative path (so Storage::disk('public')->url() works)
            // e.g. insurance/filename.ext
            if ($filename) {
                $contractor->file_path = 'insurance/'.$filename;
            }
        }

        // ---------------------------------------------------------------------
        // 5. Update basic location and profile status
        // ---------------------------------------------------------------------

        // Update the user's latitude and longitude if provided
        if ($request->filled(['latitude', 'longitude'])) {
            $user->update([
                'latitude'  => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
            ]);
        }

        $contractor->profile_completion = true;
        $contractor->save();

        // ---------------------------------------------------------------------
        // 6. Map job requirement slugs -> IDs and sync pivot
        // ---------------------------------------------------------------------
        // Incoming example:
        // job_requirements[] = ["background_check_requirement", "insurance_required", ...]
        $incomingSlugs = $request->input('job_requirements', []);

        // Fetch IDs from job_requirements table by slug column
        $jobRequirementIds = JobRequirement::whereIn('slug', $incomingSlugs)
            ->pluck('id')
            ->all();

        // Sync many-to-many pivot table contractor_job_requirement
        // This will:
        // - Attach new job requirements
        // - Detach ones not present in $jobRequirementIds
        $contractor->jobRequirements()->sync($jobRequirementIds);

        // ---------------------------------------------------------------------
        // 7. Build file URL for response
        // ---------------------------------------------------------------------
        $fileUrl = $contractor->file_path ?? null;
        // ---------------------------------------------------------------------
        // 8. Load related job requirements for response
        // ---------------------------------------------------------------------
        $requirements = $contractor->jobRequirements()
            ->select('job_requirements.id', 'job_requirements.name', 'job_requirements.slug')
            ->get();

        // Return slugs in response to match the request style
        $jobRequirementSlugs = $requirements->pluck('slug')->values()->all();

        // ---------------------------------------------------------------------
        // 9. Prepare structured response payload
        // ---------------------------------------------------------------------
        $response = [
            'id' => $contractor->id,
            'user_id' => $user->id,
            'role' => $user->user_type,
            'location' => [
                'latitude' => $user->latitude,
                'longitude' => $user->longitude,
            ],
            'file_url' => $fileUrl,
            'job_requirements' => $jobRequirementSlugs,
            'profile_completion' => (bool) $contractor->profile_completion,
            'created_at' => $contractor->created_at?->toIso8601String(),
            'updated_at' => $contractor->updated_at?->toIso8601String(),
        ];

        // ---------------------------------------------------------------------
        // 10. Return API response
        // ---------------------------------------------------------------------
        return ApiResponse::success('Contractor details updated successfully', [
            'contractor' => $response,
        ]);
    }

    /**
     * GET /api/contractor/job-requirements
     */
    public function jobRequirements()
    {
        $user = auth('api')->user();

        $requirements = JobRequirement::select('id', 'name')->get();

        return ApiResponse::success('Requirements load successfully', [
            'requirements' => $requirements,
        ]);
    }
}
