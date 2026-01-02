<?php

namespace App\Http\Controllers\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\JobPost;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    /**
     * POST /api/reviews_submit
     *
     * Headers:
     *  - Authorization: Bearer <ACCESS_TOKEN>
     *  - Content-Type: application/json
     *
     * Body:
     * {
     *   "job_id": "job_789456",
     *   "reviewer_id": "user_12345",
     *   "overall_rating": 4,
     *   "recommendation": "recommended",
     *   "ratings": {
     *     "communication": 4.0,
     *     "job_quality": 3.5,
     *     "professionalism": 4.5
     *   },
     *   "job_complete_satisfaction": true,
     *   "comment": "Excellent work! ..."
     * }
     */
    public function submitReview(Request $request)
    {
        $authUser = auth('api')->user();

        try {
            $validated = $request->validate([
                'job_id' => 'required|string',
                'reviewer_id' => 'required',  // external code; we’ll map it
                'overall_rating' => 'required|integer|min:1|max:5',
                'recommendation' => 'required|string|in:recommended,not_recommended',
                'ratings' => 'required|array',
                'ratings.communication' => 'required|numeric|min:0|max:5',
                'ratings.job_quality' => 'required|numeric|min:0|max:5',
                'ratings.professionalism' => 'required|numeric|min:0|max:5',
                'job_complete_satisfaction' => 'required|boolean',
                'comment' => 'nullable|string',
            ]); // Nested validation using dot-notation for the ratings object.[web:18][web:190]
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation error', 422, $e->errors());
        }

        // 1. Resolve job by job_code
        $jobPost = JobPost::where('job_code', $validated['job_id'])->first();

        if (! $jobPost) {
            return ApiResponse::warning('Job not found', 404);
        }

        // Check for the job status :
        // - Only allow reviews for completed jobs
        if ($jobPost->status !== 'completed') {
            return ApiResponse::warning('Reviews can only be submitted for completed jobs.', 400);
        }

        // 2. Resolve reviewer by some external code if needed.
        // For now assume reviewer_id matches auth user id (to avoid mismatch).
        if ((int) $authUser->id !== $validated['reviewer_id']) {
            // Optionally enforce that only the logged-in user can submit as themselves
            return ApiResponse::warning('Unauthorized reviewer', 403);
        }

        $reviewerId = $authUser->id;

        // 3. Compute average from ratings
        $ratings = $validated['ratings'];

        $average = round(
            (
                $ratings['communication'] +
                $ratings['job_quality'] +
                $ratings['professionalism']
            ) / 3,
            1
        ); // Average calculation and rounding is straightforward collection math.[web:425][web:417]

        // 4. Generate review_code like "review_456789"
        $reviewCode = 'review_'.mt_rand(100000, 999999);

        // 5. Create review
        $review = Review::create([
            'review_code' => $reviewCode,
            'job_post_id' => $jobPost->id,
            'reviewer_id' => $reviewerId,
            'reviewee_id' => $jobPost->user_id, // job owner as reviewee (optional)
            'overall_rating' => $validated['overall_rating'],
            'recommendation' => $validated['recommendation'],
            'communication_rating' => $ratings['communication'],
            'job_quality_rating' => $ratings['job_quality'],
            'professionalism_rating' => $ratings['professionalism'],
            'job_complete_satisfaction' => $validated['job_complete_satisfaction'],
            'comment' => $validated['comment'] ?? null,
            'average_rating' => $average,
        ]);

        // 6. Build response
        $data = [
            'review_id' => $review->review_code,
            'job_id' => $jobPost->job_code,
            'overall_rating' => (int) $review->overall_rating,
            'recommendation' => $review->recommendation,
            'average_rating' => (float) $review->average_rating,
            'created_at' => $review->created_at?->toIso8601String(),
        ];

        return ApiResponse::success('Review submitted successfully.', [
            'data' => $data,
        ]);
    }

    /**
     * GET /api/reviews
     * Query: ?user_id=user_98765&page=1&limit=10
     */
    public function listReviews(Request $request)
    {
        $userCode = $request->query('user_id');   // e.g. "user_98765"
        $jobCode = $request->query('job_id');    // e.g. "job_789456"
        $page = (int) $request->query('page', 1);
        $limit = (int) $request->query('limit', 10);
        $limit = $limit > 0 ? min($limit, 100) : 10; // safe per-page limit.[web:296][web:479]

        if (! $userCode) {
            return ApiResponse::error('Invalid or missing user_id', 422);
        }

        $revieweeId = (int) str_replace('user_', '', $userCode);

        $reviewee = User::find($revieweeId);
        if (! $reviewee) {
            return ApiResponse::error('User not found', 404);
        }

        // Base query: reviews received by this user
        $query = Review::with(['reviewer', 'jobPost'])
            ->where('reviewee_id', $revieweeId);

        // Optional job filter
        if ($jobCode ) {
            $jobPostId = JobPost::where('job_code', $jobCode)->value('id');
            if ($jobPostId) {
                $query->where('job_post_id', $jobPostId);
            } else {
                // No such job: return empty list but keep profile block valid
                $paginator = $query->paginate($limit, ['*'], 'page', $page);

                return $this->buildReviewResponse($reviewee, $paginator, $userCode);
            }
        }

        $query->orderByDesc('created_at'); // latest first.[web:375]

        $paginator = $query->paginate($limit, ['*'], 'page', $page);

        return $this->buildReviewResponse($reviewee, $paginator, $userCode);
    }

    protected function buildReviewResponse(User $reviewee, \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator, string $userCode)
    {
        $reviewsCollection = $paginator->getCollection();

        // Summary stats over ALL reviews for this user (not just this page / job)
        $averageRating = round(
            $reviewee->receivedReviews()->avg('overall_rating') ?? 0,
            1
        ); // using avg() on relationship is standard for ratings.[web:436][web:484]

        $totalReviews = $reviewee->receivedReviews()->count();

        $recentlyReviewed = $reviewee->receivedReviews()
            ->where('created_at', '>=', now()->subDays(30))
            ->exists(); // simple “recently reviewed” flag based on last 30 days.[web:489]

        $reviews = $reviewsCollection->map(function (Review $review) {
            $reviewer = $review->reviewer;
            $jobPost = $review->jobPost;

            return [
                'review_id' => $review->review_code,
                'reviewer' => [
                    'user_id' => $reviewer ? 'user_'.$reviewer->id : null,
                    'name' => $reviewer->name ?? '',
                    'profile_image' => $reviewer->profile_image ?? null,
                ],
                'job_title' => $jobPost->title ?? '',
                'rating' => (int) $review->overall_rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at?->toIso8601String(),
            ];
        })->values()->all(); // mapping via collection keeps controller logic clean.[web:375]

        $data = [
            'profile' => [
                'user_id' => $userCode,
                'name' => $reviewee->name ?? '',
                'profile_image' => $reviewee->profile_image ?? null,
                'average_rating' => $averageRating,
                'total_reviews' => $totalReviews,
                'recently_reviewed' => (bool) $recentlyReviewed,
            ],
            'reviews' => $reviews,
            'pagination' => [
                'page' => $paginator->currentPage(),
                'limit' => $paginator->perPage(),
                'total_pages' => $paginator->lastPage(),
                'total_reviews' => $paginator->total(),
            ],
        ];

        return ApiResponse::success('', ['data' => $data]);
    }

    public function listReviewsOld(Request $request)
    {
        // 1. Parse and validate query
        $userId = $request->query('user_id'); // e.g. "user_98765"
        $page = (int) $request->query('page', 1);
        $limit = (int) $request->query('limit', 10);
        $limit = $limit > 0 ? min($limit, 100) : 10; // cap page size.[web:296][web:432]

        if (! $userId) {
            return ApiResponse::error('user_id is required', 422);
        }

        // For now assume external user code is "user_{$id}"
        if (! str_starts_with($userId, 'user_')) {
            return ApiResponse::error('Invalid user_id format', 422);
        }
        $revieweeId = (int) str_replace('user_', '', $userId);

        // 2. Load reviewee profile
        $reviewee = User::find($revieweeId);
        if (! $reviewee) {
            return ApiResponse::error('User not found', 404);
        }

        // 3. Query reviews where this user is reviewee
        $query = Review::with(['reviewer', 'jobPost'])
            ->where('reviewee_id', $revieweeId)
            ->orderByDesc('created_at');

        $paginator = $query->paginate($limit, ['*'], 'page', $page); // standard paginator.[web:296][web:305]

        $reviewsCollection = $paginator->getCollection();

        // 4. Compute summary stats
        $averageRating = round(
            $reviewee->receivedReviews()->avg('overall_rating') ?? 0,
            1
        ); // avg() is the typical way to get average rating per user.[web:433][web:436]

        $totalReviews = $reviewee->receivedReviews()->count();

        // Simple heuristic for "recently_reviewed"
        $recentlyReviewed = $reviewee->receivedReviews()
            ->where('created_at', '>=', now()->subDays(30))
            ->exists();

        // 5. Map reviews
        $reviews = $reviewsCollection->map(function (Review $review) {
            $reviewer = $review->reviewer;
            $jobPost = $review->jobPost;

            return [
                'review_id' => $review->review_code,
                'reviewer' => [
                    'user_id' => 'user_'.$reviewer?->id,
                    'name' => $reviewer?->name ?? '',
                    'profile_image' => $reviewer?->profile_image ?? null,
                ],
                'job_title' => $jobPost?->title ?? '',
                'rating' => (int) $review->overall_rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at?->toIso8601String(),
            ];
        })->values()->all(); // map() is standard for shaping Eloquent data for APIs.[web:394][web:388]

        // 6. Build response payload
        $data = [
            'profile' => [
                'user_id' => $userId,
                'name' => $reviewee->name ?? '',
                'profile_image' => $reviewee->profile_image ?? null,
                'average_rating' => $averageRating,
                'total_reviews' => $totalReviews,
                'recently_reviewed' => (bool) $recentlyReviewed,
            ],
            'reviews' => $reviews,
            'pagination' => [
                'page' => $paginator->currentPage(),
                'limit' => $paginator->perPage(),
                'total_pages' => $paginator->lastPage(),
                'total_reviews' => $paginator->total(),
            ],
        ];

        return ApiResponse::success('', ['data' => $data]);
    }
}
