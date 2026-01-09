<?php

namespace App\Http\Controllers\V1;

use App\Helpers\FileUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\ApprenticeProfile;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class ApprenticeProfileController extends Controller
{
    /**
     * POST /api/create_apprentice_profile
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createApprenticeProfile(Request $request)
    {
        $user = auth('api')->user();

        $validated = $request->validate([
            'position_seeking' => 'required|string|max:255',
            'age' => 'required|integer|min:14|max:100',
            'location' => 'required|array',
            'location.lat' => 'required|numeric|between:-90,90',
            'location.lng' => 'required|numeric|between:-180,180',
            'location.city' => 'required|string|max:255',
            'education_experience' => 'nullable|string|max:255',
            'trade_school' => 'nullable|string|max:255',
            'about_me' => 'nullable|string',
            'resume_file_url' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'profile_visible' => 'required|boolean',
        ]); // File validation with mimes/max is the standard approach for uploads.[web:105][web:108]

        $location = $validated['location'];

        // Handle resume upload via helper (if provided)
        $resumeUrl = null;
        if ($request->hasFile('resume_file_url')) {
            $resumeUrl = FileUploadHelper::upload(
                $request->file('resume_file_url'),
                'resumes/apprentices'
            ); // Helper encapsulates Storage logic, paths, visibility, etc.[web:100][web:103]
        }

        $nextNumericId = (ApprenticeProfile::max('id') ?? 0) + 1;
        $publicId = 'apprentice_'.str_pad((string) $nextNumericId, 3, '0', STR_PAD_LEFT);

        $profile = ApprenticeProfile::create([
            'public_id' => $publicId,
            'user_id' => $user->id,
            'position_seeking' => $validated['position_seeking'],
            'age' => $validated['age'],
            'lat' => $location['lat'],
            'lng' => $location['lng'],
            'city' => $location['city'],
            'location_text' => null,
            'education_experience' => $validated['education_experience'] ?? null,
            'trade_school' => $validated['trade_school'] ?? null,
            'about_me' => $validated['about_me'] ?? null,
            'resume_file_url' => $resumeUrl,
            'profile_visible' => $validated['profile_visible'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Apprentice profile created successfully.',
            'profile_id' => $profile->public_id,
            'created_at' => $profile->created_at?->toIso8601String(),
            'data' => [
                'position_seeking' => $profile->position_seeking,
                'age' => $profile->age,
                'location' => [
                    'lat' => $profile->lat,
                    'lng' => $profile->lng,
                    'city' => $profile->city,
                ],
                'education_experience' => $profile->education_experience,
                'trade_school' => $profile->trade_school,
                'about_me' => $profile->about_me,
                'resume_file_url' => $profile->resume_file_url,
                'profile_visible' => $profile->profile_visible,
            ],
        ], 201);
    }

    /**
     * POST /api/edit_apprentice_profile
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function editApprenticeProfile(Request $request)
    {
        $user = auth('api')->user();

        $validated = $request->validate([
            'id' => 'required|string',
            'position_seeking' => 'nullable|string|max:255',
            'age' => 'nullable|integer|min:14|max:100',
            'location' => 'nullable|string|max:255',
            'education_experience' => 'nullable|string|max:255',
            'trade_school' => 'nullable|string|max:255',
            'about_me' => 'nullable|string',
            'resume_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'profile_visible' => 'nullable|boolean',
        ]);

        $profile = ApprenticeProfile::where('public_id', $validated['id'])->first();

        if (! $profile) {
            return response()->json([
                'status' => 'error',
                'message' => 'Apprentice profile not found.',
            ], 404);
        }

        if ($profile->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ], 403);
        }

        // Partial field updates
        if (array_key_exists('position_seeking', $validated)) {
            $profile->position_seeking = $validated['position_seeking'];
        }
        if (array_key_exists('age', $validated)) {
            $profile->age = $validated['age'];
        }
        if (array_key_exists('location', $validated)) {
            $profile->location_text = $validated['location'];
        }
        if (array_key_exists('education_experience', $validated)) {
            $profile->education_experience = $validated['education_experience'];
        }
        if (array_key_exists('trade_school', $validated)) {
            $profile->trade_school = $validated['trade_school'];
        }
        if (array_key_exists('about_me', $validated)) {
            $profile->about_me = $validated['about_me'];
        }
        if (array_key_exists('profile_visible', $validated)) {
            $profile->profile_visible = $validated['profile_visible'];
        }

        // New resume upload (overwrite URL)
        if ($request->hasFile('resume_file')) {
            $resumeUrl = FileUploadHelper::upload(
                $request->file('resume_file'),
                'resumes/apprentices'
            ); // Delegate storage concerns to the helper for consistency.[web:100][web:111]

            $profile->resume_file_url = $resumeUrl;
        }

        $profile->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Apprentice profile updated successfully.',
            'id' => $profile->public_id,
            'updated_at' => $profile->updated_at?->toIso8601String(),
            'data' => [
                'position_seeking' => $profile->position_seeking,
                'age' => $profile->age,
                'location' => $profile->location_text ?? $profile->city,
                'education_experience' => $profile->education_experience,
                'trade_school' => $profile->trade_school,
                'about_me' => $profile->about_me,
                'resume_file_url' => $profile->resume_file_url,
                'profile_visible' => $profile->profile_visible,
            ],
        ]);
    }

    /*
            * GET /api/get_apprentice_profile
            * @param Request $request
            * @return \Illuminate\Http\JsonResponse
            */
    public function getApprenticeProfile(Request $request)
    {
        $user = auth('api')->user();

        $profile = ApprenticeProfile::where('user_id', $user->id)->first();

        if (! $profile) {
            return response()->json([
                'status' => 'success',
                'message' => 'Apprentice profile not found.',
                'data' => null,
            ]);
        }

        $location = $profile->location_text
            ? $profile->location_text
            : ($profile->city ?? null);

        return response()->json([
            'status' => 'success',
            'message' => 'Apprentice profile fetched successfully.',
            'data' => [
                'id' => $profile->public_id,
                'position_seeking' => $profile->position_seeking,
                'age' => $profile->age,
                'location' => $location,
                'education_experience' => $profile->education_experience,
                'trade_school' => $profile->trade_school,
                'about_me' => $profile->about_me,
                'resume_file_url' => $profile->resume_file_url,
                'profile_visible' => $profile->profile_visible,
                'created_at' => $profile->created_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * POST /api/delete_apprentice_profile
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteApprenticeProfile(Request $request)
    {
        $user = auth('api')->user();

        $validated = $request->validate([
            'id' => 'required|string',
        ]);

        $profile = ApprenticeProfile::where('public_id', $validated['id'])->first();

        if (! $profile) {
            return response()->json([
                'status' => 'error',
                'message' => 'Apprentice profile not found.',
            ], 404);
        }

        if ($profile->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ], 403);
        }
        $path = public_path('resumes/apprentices/' . $profile->resume_file_url ?? '');

        if (File::exists($path)) {
            File::delete($path);
        } // Clean up uploaded resume file.

        $profile->delete(); // sets deleted_at via SoftDeletes.[web:74][web:77]

        return response()->json([
            'status' => 'success',
            'message' => 'Apprentice profile deleted successfully.',
            'deleted_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * GET /api/get_all_apprentice_profiles?page=&limit=
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllApprenticeProfiles(Request $request)
    {
        $page = (int) $request->query('page', 1);
        $limit = (int) $request->query('limit', 10);
        $limit = $limit > 0 ? min($limit, 100) : 10; // common per-page cap.[web:81][web:67]

        $paginator = ApprenticeProfile::with('user')
            ->where('profile_visible', true)
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        $items = $paginator->getCollection()->map(function (ApprenticeProfile $profile) {
            $user = $profile->user;

            return [
                'id' => $profile->public_id,
                'full_name' => $user?->name,
                'profile_image_url' => $user?->profile_image,
                'position_seeking' => $profile->position_seeking,
                'age' => $profile->age,
                'location' => $profile->location_text
                                          ?? $profile->city,
                'education_experience' => $profile->education_experience,
                'trade_school' => $profile->trade_school,
                'about_me' => $profile->about_me,
                'resume_file_url' => $profile->resume_file_url,
                'profile_visible' => $profile->profile_visible,
                'created_at' => $profile->created_at?->toIso8601String(),
            ];
        })->values()->all(); // mapping Eloquent results to API resources is standard.[web:39][web:32]

        return response()->json([
            'status' => 'success',
            'message' => 'Apprentice profiles fetched successfully.',
            'count' => count($items),
            'data' => $items,
        ]);
    }
}
