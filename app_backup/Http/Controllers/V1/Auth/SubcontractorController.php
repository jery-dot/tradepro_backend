<?php

namespace App\Http\Controllers\V1\Auth;

use App\Enums\UserType;
use App\Helpers\ApiResponse;
use App\Helpers\FileUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\Subcontractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubcontractorController extends Controller
{
    /**
     * POST /api/subcontractor_update_details
     * Content-Type: multipart/form-data
     */
    public function updateDetails(Request $request)
    {
        $user = auth('api')->user();

        // Only sub-contractor (user_type = 1) can access this endpoint
        if ($user->user_type !== UserType::SUBCONTRACTOR) {
            return ApiResponse::error('Unauthorized', 403);
        }

        $validated = $request->validate([
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'insurance_file' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
        ]);

        $path = null;

        // ---------------------------------------------------------------------
        // Get or create sub contractor profile for this user
        // ---------------------------------------------------------------------
        $subcontractor = SubContractor::firstOrCreate(
            ['user_id' => $user->id],
            [] // default attributes when creating new record
        );

        // ---------------------------------------------------------------------
        // Handle file upload if provided (using helper)
        // ---------------------------------------------------------------------
        if ($request->hasFile('insurance_file')) {

            // Existing file name (if any) so helper can delete it
            $oldFile = $subcontractor->insurance_file_path
                ? basename($subcontractor->insurance_file_path)
                : null;

            // Upload new file into public/insurance (or storage/insurance if you prefer)
            $filename = FileUploadHelper::upload(
                $request->file('insurance_file'),
                'insurance',                    // directory inside public/
                $oldFile,
                'subcontractor_'.$subcontractor->id
            );

            if ($filename) {
                // Save relative path; later you can build full URL with asset() or Storage
                $subcontractor->insurance_file_path = 'insurance/'.$filename;
            }
        }

        // Update the user's latitude and longitude if provided
        if ($request->filled(['latitude', 'longitude'])) {
            $user->update([
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
            ]);
        }

        //
        $subcontractor = Subcontractor::updateOrCreate(
            ['user_id' => $user->id],
            [
                'insurance_file_path' => $path ?? $request->input('insurance_file_path', $subcontractor->insurance_file_path ?? null),
                'profile_completion' => true,
            ]
        );

        // Build public URL to file
        // $fileUrl = $subcontractor->insurance_file_path
        //     ? Storage::disk('public')->url($subcontractor->insurance_file_path)
        //     : null;
        $fileUrl = $subcontractor->insurance_file_path ?? null;

        $response = [
            'id' => $subcontractor->id,
            'user_id' => $user->id,
            'role' => $user->user_type,
            'location' => [
                'latitude' => $user->latitude,
                'longitude' => $user->longitude,
            ],
            'insurance_file_url' => $fileUrl,
            'profile_completion' => (bool) $subcontractor->profile_completion,
            'created_at' => $subcontractor->created_at?->toIso8601String(),
            'updated_at' => $subcontractor->updated_at?->toIso8601String(),
        ];

        return ApiResponse::success('Subcontractor details updated successfully', [
            'subcontractor' => $response,
        ]);
    }
}
