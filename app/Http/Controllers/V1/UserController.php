<?php

namespace App\Http\Controllers\V1;

use App\Enums\UserType;
use App\Helpers\FileUploadHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Summary of updateProfileImage
     * POST /api/update_profile_image
     *
     * Request Body (Form-Data):
     * -id = user_001
     * -profile_image = user_001_updated.png
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfileImage(Request $request)
    {
        $user = auth('api')->user();

        $validated = $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]); // standard image validation.[web:17][web:11]

        $url = FileUploadHelper::upload($request->file('profile_image'), 'profiles');

        $user->profile_image = $url;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profile image updated successfully.',
            'id' => 'user_'.$user->id,
            'profile_image_url' => $user->profile_image,
            'updated_at' => $user->updated_at?->toIso8601String(),
        ]);
    }

    /**
     * Summary of updateJobRequirement
     *
     * POST /api/update_job_requirement
     *
     * - "id": "user_001",
     * - "job_requirements": [
                    "Background check requirement",
                    "Insurance required",
                    "Safety certification"
                ]
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateJobRequirement(Request $request)
    {
        $user = auth('api')->user();

        $validated = $request->validate([
            'job_requirements' => 'required|array|min:1',
            'job_requirements.*' => 'string|max:255',
        ]);

        $user->job_requirements = $validated['job_requirements'];
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Typical job requirements updated successfully.',
            'id' => 'user_'.$user->id,
            'updated_at' => $user->updated_at?->toIso8601String(),
            'data' => [
                'job_requirements' => $user->job_requirements,
            ],
        ]);
    }

    /**
     * Summary of updateProfileDocument
     * POST /api/update_profile_document
     *
     * id = user_001
     * profile_image = user_001_document.pdf
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfileDocument(Request $request)
    {
        $user = auth('api')->user();

            $validated = $request->validate([
                'insurance_file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            ]); // typical file validation for documents.[web:105][web:111]

        if ($user->user_type == UserType::CONTRACTOR) {

            // Old file (if any) to be deleted after successful upload
            $oldFile = $user->contractor->file_path
                ? basename($user->contractor->file_path)   // only filename, without directory
                : null;

            // Upload new file to public/insurance via helper
            $filename = FileUploadHelper::upload(
                $request->file('insurance_file'),
                'insurance',   // directory relative to public/
                $oldFile,
                'contractor_'.$user->contractor->id
            );

            // Persist relative path (so Storage::disk('public')->url() works)
            // e.g. insurance/filename.ext
            if ($filename) {
                $user->contractor->file_path = 'insurance/'.$filename;
            }

            $user->contractor->save();

            $file_info = explode('/', $user->contractor->file_path);

            $API_URL = 'https://api.tradepro.services/';

            return response()->json([
                'status' => 'success',
                'message' => 'Profile document updated successfully.',
                'id' => 'user_'.$user->id,
                'document_url' => $API_URL.$user->contractor->file_path,
                'file_name' => $file_info[1],
                // 'file_size' => $user->contractor->file_size,
                'updated_at' => $user->updated_at?->toIso8601String(),
            ]);
        } elseif ($user->user_type == UserType::SUBCONTRACTOR) {

            // Old file (if any) to be deleted after successful upload
            $oldFile = $user->subcontractor->insurance_file_path
                ? basename($user->subcontractor->insurance_file_path)   // only filename, without directory
                : null;

            // Upload new file to public/insurance via helper
            $filename = FileUploadHelper::upload(
                $request->file('insurance_file'),
                'insurance',   // directory relative to public/
                $oldFile,
                'subcontractor_'.$user->subcontractor->id
            );

            // Persist relative path (so Storage::disk('public')->url() works)
            // e.g. insurance/filename.ext
            if ($filename) {
                $user->subcontractor->insurance_file_path = 'insurance/'.$filename;
            }

            $user->subcontractor->save();

            $file_info = explode('/', $user->subcontractor->insurance_file_path);

            $API_URL = 'https://api.tradepro.services/';

            return response()->json([
                'status' => 'success',
                'message' => 'Profile document updated successfully.',
                'id' => 'user_'.$user->id,
                'document_url' => $API_URL.$user->subcontractor->insurance_file_path,
                'file_name' => $file_info[1],
                // 'file_size' => $user->subcontractor->file_size,
                'updated_at' => $user->updated_at?->toIso8601String(),
            ]);
        }

        return response()->json([
            "status" => false,
            "message" => "Unauthorized"
        ], 401);
    }

    /**
     * Get contractor profile details.
     *
     * Endpoint:
     * GET /api/get_contractor_profile
     * Headers:
     *   Authorization: Bearer <ACCESS_TOKEN>
     *   Content-Type: application/json
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getContractorProfile(Request $request)
    {
        $user = auth('api')->user();

        if ($user->user_type !== UserType::CONTRACTOR) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
        }

        $ratingsCount = $user->receivedReviews()->count();
        $rating = round($user->receivedReviews()->avg('overall_rating') ?? 0, 1);

        // ---------------------------------------------------------------------
        // Load related job requirements for response
        // ---------------------------------------------------------------------
        $requirements = $user->contractor->jobRequirements()
            ->select('job_requirements.id', 'job_requirements.name', 'job_requirements.slug')
            ->get();

        // Return slugs in response to match the request style
        $jobRequirementSlugs = $requirements->pluck('name')->values()->all();

        return response()->json([
            'status' => 'success',
            'message' => 'Contractor profile fetched successfully.',
            'data' => [
                'id' => 'user_'.$user->id,
                'full_name' => $user->name,
                'role' => $user->role_label,
                'rating' => $rating,
                'profile_image_url' => $user->profile_image,
                'location' => [
                    'latitude' => $user->latitude,
                    'longitude' => $user->longitude,
                ],
                'uploaded_document' => $user->contractor->file_path,
                'job_requirement' => $jobRequirementSlugs,
                'ratings_count' => $ratingsCount,
                'created_at' => $user->created_at?->toIso8601String(),
                'updated_at' => $user->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get subcontractor profile details.
     *
     * Endpoint:
     * GET /api/get_subcontractor_profile
     * Headers:
     *   Authorization: Bearer <ACCESS_TOKEN>
     *   Content-Type: application/json
     *
     * Response:
     * {
     *   "status": "success",
     *   "message": "Subcontractor data fetched successfully.",
     *   "data": {
     *     "id": "user_001",
     *     "full_name": "Sahil Mehta",
     *     "role": "Subcontractor",
     *     "rating": 4.4,
     *     "profile_image_url": "https://example.com/profile/user_001.png",
     *     "location": "London, UK",
     *     "uploaded_document": {
     *       "file_name": "test.pdf",
     *       "file_size": "0.01 MB",
     *       "document_url": "https://example.com/uploads/test.pdf"
     *     },
     *     "ratings_count": 22,
     *     "created_at": "2025-11-20T09:30:00Z",
     *     "updated_at": "2026-01-12T15:20:00Z"
     *   }
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubcontractorProfile(Request $request)
    {
        $user = auth('api')->user();

        if ($user->user_type !== UserType::SUBCONTRACTOR) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
        }

        $ratingsCount = $user->receivedReviews()->count();
        $rating = round($user->receivedReviews()->avg('overall_rating') ?? 0, 1);

        return response()->json([
            'status' => 'success',
            'message' => 'Subcontractor data fetched successfully.',
            'data' => [
                'id' => 'user_'.$user->id,
                'full_name' => $user->name,
                'role' => $user->role_label,
                'rating' => $rating,
                'profile_image_url' => $user->profile_image,
                'location' => $user->location_text,
                'uploaded_document' => $user->uploaded_document,
                'ratings_count' => $ratingsCount,
                'created_at' => $user->created_at?->toIso8601String(),
                'updated_at' => $user->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get apprentice profile details.
     *
     * Endpoint:
     * GET /api/get_apprentice_profile
     * Headers:
     *   Authorization: Bearer <ACCESS_TOKEN>
     *   Content-Type: application/json
     *
     * Response:
     * {
     *   "status": "success",
     *   "message": "Apprentice profile fetched successfully.",
     *   "data": {
     *     "id": "user_001",
     *     "full_name": "Sahil Mehta",
     *     "role": "Apprentice",
     *     "rating": 4.4,
     *     "profile_image_url": "https://example.com/profile/user_001.png",
     *     "school_name": "Rice School, UK",
     */
    public function getApprenticeProfile(Request $request)
    {
        $user = auth('api')->user();

        if ($user->user_type !== UserType::APPRENTICE) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
        }

        // Use your apprentice() HasOne relation for school/program fields
        $apprentice = $user->apprentice;

        return response()->json([
            'status' => 'success',
            'message' => 'Apprentice profile fetched successfully.',
            'data' => [
                'id' => 'user_'.$user->id,
                'full_name' => $user->name,
                'role' => $user->role_label,
                'rating' => $user->ratings_data['rating'],
                'profile_image_url' => $user->profile_image,
                'school_name' => $apprentice?->school_name,
                'program_name' => $apprentice?->program_name,
                'experience_level' => $apprentice?->experience_level,
                'created_at' => $user->created_at?->toIso8601String(),
                'updated_at' => $user->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Delete Account API.
     *
     * Endpoint:
     * POST /api/delete_account
     *
     * Headers:
     *   Authorization: Bearer <ACCESS_TOKEN>
     *   Content-Type: application/json
     *
     * This permanently deletes the authenticated user's account and all related data
     * (listings, opportunities, profiles, reviews, notifications).
     *
     * Success Response:
     * {
     *   "status": "success",
     *   "message": "Account deleted successfully.",
     *   "user_id": "contractor_001",
     *   "deleted_at": "2025-10-01T14:32:10Z"
     * }
     *
     * Error Responses:
     * {
     *   "status": "error",
     *   "message": "User not found.",
     *   "error_code": "USER_NOT_FOUND"
     * }
     * {
     *   "status": "error",
     *   "message": "Invalid or missing access token.",
     *   "error_code": "UNAUTHORIZED"
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAccount(Request $request)
    {
        try {
            $user = auth('api')->user();

            if (! $user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or missing access token.',
                    'error_code' => 'UNAUTHORIZED',
                ], 401);
            }

            // Hard delete user + cascade deletes everything else
            $user->delete(); // triggers onDelete('cascade') on foreign keys.[web:39][web:13]

            return response()->json([
                'status' => 'success',
                'message' => 'Account deleted successfully.',
                'user_id' => $user->id, // helper: contractor_001 format
                'deleted_at' => now()->toIso8601String(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                // 'message' => 'User not found.',
                'error_code' => 'USER_NOT_FOUND',
            ], 404);
        }
    }

    /**
     * Helper to format user_id as contractor_001, apprentice_002, etc.
     */
    public function getFormattedId(): string
    {
        $roleLabels = [
            0 => 'contractor',
            1 => 'subcontractor',
            2 => 'laborer',
            3 => 'apprentice',
        ];

        return ($roleLabels[$this->user_type->value] ?? 'user').'_'.$this->id;
    }

    /**
     * Get User Settings API.
     *
     * Endpoint:
     * GET /api/get_user_settings
     *
     * Headers:
     *   Authorization: Bearer <ACCESS_TOKEN>
     *   Content-Type: application/json
     *
     * Response:
     * {
     *   "status": "success",
     *   "message": "User settings fetched successfully.",
     *   "data": {
     *     "notification_status": true,
     *     "subscription_status": "active"
     *   }
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserSettings(Request $request)
    {
        $user = auth('api')->user();

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or missing access token.',
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User settings fetched successfully.',
            'data' => [
                'notification_status' => (bool) $user->notification_status ?? true,
                'subscription_status' => $user->subscription_status ?? 'inactive',
            ],
        ]);
    }

    /**
     * Update Notification Status API.
     *
     * Endpoint:
     * POST /api/update_notification_status
     *
     * Headers:
     *   Authorization: Bearer <ACCESS_TOKEN>
     *   Content-Type: application/json
     *
     * Request Body:
     * {
     *   "notification_status": true
     * }
     *
     * Response:
     * {
     *   "status": "success",
     *   "message": "Notification status updated successfully.",
     *   "data": {
     *     "notification_status": true,
     *     "updated_at": "2025-10-01T10:15:30Z"
     *   }
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateNotificationStatus(Request $request)
    {
        $user = auth('api')->user();

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or missing access token.',
            ], 401);
        }

        $validated = $request->validate([
            'notification_status' => 'required|boolean',
        ]);

        // Update user's notification preference
        $user->notification_status = $validated['notification_status'];
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification status updated successfully.',
            'data' => [
                'notification_status' => $user->notification_status,
                'updated_at' => $user->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Update User Location API.
     *
     * Endpoint:
     * POST /api/update_location
     *
     * Headers:
     *   Authorization: Bearer <ACCESS_TOKEN>
     *   Content-Type: application/json
     *
     * Request Body:
     * {
     *   "latitude": 40.712776,
     *   "longitude": -74.005974,
     *   "address": "New York, NY, USA"
     * }
     *
     * Response:
     * {
     *   "status": "success",
     *   "message": "Location updated successfully.",
     *   "data": {
     *     "latitude": 40.712776,
     *     "longitude": -74.005974,
     *     "address": "New York, NY, USA",
     *     "updated_at": "2025-10-01T12:20:15Z"
     *   }
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateLocationOld(Request $request)
    {
        $user = auth('api')->user();

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or missing access token.',
            ], 401);
        }

        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'required|string|max:255',
        ]);

        // Update user's location fields
        $user->latitude = $validated['latitude'];
        $user->longitude = $validated['longitude'];
        $user->location_text = $validated['address'];
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Location updated successfully.',
            'data' => [
                'latitude' => (float) $user->latitude,
                'longitude' => (float) $user->longitude,
                'address' => $user->location_text,
                'updated_at' => $user->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Update User Location API.
     *
     * Endpoint:
     * POST /api/update_location
     *
     * Headers:
     *   Authorization: Bearer <ACCESS_TOKEN>
     *   Content-Type: application/json
     *
     * Request Body:
     * {
     *   "latitude": 40.712776,
     *   "longitude": -74.005974,
     *   "address": "New York, NY, USA"
     * }
     *
     * Note: address is comma-separated "city, state, country"
     *
     * Response:
     * {
     *   "status": "success",
     *   "message": "Location updated successfully.",
     *   "data": {
     *     "latitude": 40.712776,
     *     "longitude": -74.005974,
     *     "address": "New York, NY, USA",
     *     "updated_at": "2025-10-01T12:20:15Z"
     *   }
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateLocation(Request $request)
    {
        $user = auth('api')->user();

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or missing access token.',
            ], 401);
        }

        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'required|string|max:255',
        ]);

        // Parse comma-separated address: "New York, NY, USA" → city, state, country
        $addressParts = array_map('trim', explode(',', $validated['address']));

        $city = $addressParts[0] ?? null;
        $state = $addressParts[1] ?? null;
        $country = $addressParts[2] ?? null;

        // Update user's location fields
        $user->latitude = $validated['latitude'];
        $user->longitude = $validated['longitude'];
        $user->city = $city;
        $user->state = $state;
        $user->country = $country;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Location updated successfully.',
            'data' => [
                'latitude' => (float) $user->latitude,
                'longitude' => (float) $user->longitude,
                'address' => $user->location_text,
                'updated_at' => $user->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Update User FCM Token API.
     *
     * Endpoint:
     * POST /api/update_fcm_token
     *
     * Headers:
     *   Authorization: Bearer <ACCESS_TOKEN>
     *   Content-Type: application/json
     *
     * Request Body:
     * {
     *   "fcm_token": "eFdA9WsmP7xkYt8yZQw123_sample_token"
     * }
     *
     * Response:
     * {
     *   "status": "success",
     *   "message": "FCM token updated successfully.",
     *   "data": {
     *     "fcm_token": "eFdA9WsmP7xkYt8yZQw123_sample_token",
     *     "updated_at": "2025-10-01T10:45:22Z"
     *   }
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateFcmToken(Request $request)
    {
        $user = auth('api')->user();

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or missing access token.',
            ], 401);
        }

        $validated = $request->validate([
            'fcm_token' => 'required|string|max:255',
        ]);

        // Update FCM token for push notifications
        $user->fcm_token = $validated['fcm_token'];
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'FCM token updated successfully.',
            'data' => [
                'fcm_token' => $user->fcm_token,
                'updated_at' => $user->updated_at?->toIso8601String(),
            ],
        ]);
    }
}
