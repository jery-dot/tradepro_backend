<?php

namespace App\Http\Controllers\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $page = (int) $request->query('page', 1);
        $limit = (int) $request->query('limit', 10);
        $limit = $limit > 0 ? min($limit, 100) : 10;
        $categoryId = $request->query('category_id');
        $search = trim((string) $request->query('search', ''));

        $query = Listing::with(['owner', 'images'])  // eager load to avoid N+1.[web:39][web:27]
            ->where('status', 'active');

        if ($categoryId !== null && $categoryId !== '') {
            $query->where('category_id', $categoryId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            }); // classic search + pagination pattern.[web:29][web:26]
        }

        $paginator = $query
            // ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->paginate($limit, ['*'], 'page', $page); // standard Eloquent pagination.[web:35]

        $listings = $paginator->getCollection()->map(function (Listing $listing) {
            $owner = $listing->owner;

            // seller rating + total reviews
            $rating = 0.0;
            $totalReview = 0;

            if ($owner) {
                $rating = round($owner->receivedReviews()->avg('overall_rating') ?? 0, 1);
                $totalReview = $owner->receivedReviews()->count(); // avg + count for seller details.[web:30][web:33]
            }

            return [
                'listing_id' => $listing->listing_code,
                'title' => $listing->title,
                'price' => (float) $listing->price,
                'currency' => $listing->currency,
                'location' => $listing->location_name,
                'images' => $listing->images->pluck('path')->values()->all(),
                'category' => [
                    'id' => $listing->category_id,
                    'name' => $listing->category_name,
                ],
                'condition' => [
                    'id' => $listing->condition_id,
                    'name' => $listing->condition_name,
                ],
                'seller' => $owner ? [
                    'user_id' => $owner->id,
                    'name' => $owner->name,
                    'rating' => $rating,
                    'total_review' => $totalReview,
                ] : null,
                'is_featured' => (bool) $listing->is_featured,
                'created_at' => $listing->created_at?->toIso8601String(),
            ];
        })->values()->all();

        $data = [
            'listings' => $listings,
            'pagination' => [
                'page' => $paginator->currentPage(),
                'limit' => $paginator->perPage(),
                'total_pages' => $paginator->lastPage(),
                'total_records' => $paginator->total(),
            ],
        ];

        /*return response()->json([
            'status'  => true,
            'message' => 'Listings fetched successfully',
            'data'    => $data,
        ]);*/

        return ApiResponse::success('Listings fetched successfully', $data, 200);
    }

    /**
     * POST /api/add_listings
     */
    public function createListing(Request $request)
    {
        $user = auth('api')->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'category_name' => 'required|string|max:255',
            'condition_id' => 'required|integer',
            'condition_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'location' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'description' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]); // Array image validation pattern for multipart form-data.[web:1][web:5][web:11]

        return DB::transaction(function () use ($validated, $request, $user) {
            $listing = Listing::create([
                'listing_code' => 'list_'.Str::random(6),
                'user_id' => $user->id,
                'title' => $validated['title'],
                'category_id' => $validated['category_id'],
                'category_name' => $validated['category_name'],
                'condition_id' => $validated['condition_id'],
                'condition_name' => $validated['condition_name'],
                'price' => $validated['price'],
                'currency' => 'USD',
                'location_name' => $validated['location'],
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'description' => $validated['description'] ?? null,
                'status' => 'active',
            ]);

            $imagesPayload = [];

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $imageFile) {
                    // $path = $imageFile->store('listings', 'public'); // store under storage/app/public/listings.

                    $fileName = 'img_'.Str::random(10).'.'.$imageFile->getClientOriginalExtension();
                    $destinationPath = public_path('listings');

                    // create directory if not exists
                    if (! file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }

                    // move file to public/listings
                    $imageFile->move($destinationPath, $fileName);

                    $path = 'listings/'.$fileName;

                    $img = ListingImage::create([
                        'listing_id' => $listing->id,
                        'image_code' => 'img_'.Str::random(5),
                        'path' => $path,
                    ]);

                    $imagesPayload[] = [
                        'id' => $img->image_code,
                        'url' => '/'.$path,
                    ];
                }
            }

            $data = [
                'listing_id' => $listing->listing_code,
                'title' => $listing->title,
                'price' => (float) $listing->price,
                'currency' => $listing->currency,
                'category' => [
                    'id' => $listing->category_id,
                    'name' => $listing->category_name,
                ],
                'condition' => [
                    'id' => $listing->condition_id,
                    'name' => $listing->condition_name,
                ],
                'location' => [
                    'name' => $listing->location_name,
                    'latitude' => $listing->latitude,
                    'longitude' => $listing->longitude,
                ],
                'images' => $imagesPayload,
                'status' => $listing->status,
                'created_at' => $listing->created_at?->toIso8601String(),
            ];

            return ApiResponse::success('Listing created successfully', ['data' => $data]);
        });
    }

    public function editListing(Request $request)
    {
        $user = auth('api')->user();

        $validated = $request->validate([
            'listing_id' => 'required|string',
            'title' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer',
            'category_name' => 'nullable|string|max:255',
            'condition_id' => 'nullable|integer',
            'condition_name' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'description' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]); // Optional fields allow partial updates.[web:3][web:11]

        $listing = Listing::where('listing_code', $validated['listing_id'])->first();

        if (! $listing) {
            return ApiResponse::error('Listing not found', 404);
        }

        if ($listing->user_id !== $user->id) {
            return ApiResponse::error('Unauthorized', 403);
        }

        return DB::transaction(function () use ($listing, $validated, $request) {
            // Update only if present
            foreach ([
                'title',
                'category_id',
                'category_name',
                'condition_id',
                'condition_name',
                'price',
                'description',
            ] as $field) {
                if (array_key_exists($field, $validated)) {
                    $listing->{$field} = $validated[$field];
                }
            }

            if (array_key_exists('location', $validated)) {
                $listing->location_name = $validated['location'];
            }
            if (array_key_exists('latitude', $validated)) {
                $listing->latitude = $validated['latitude'];
            }
            if (array_key_exists('longitude', $validated)) {
                $listing->longitude = $validated['longitude'];
            }

            $listing->save();

            $imagesPayload = [];

            // Option: clear and replace existing images, or just append; here we replace.
            if ($request->hasFile('images')) {
                // delete old images records (and optionally files)
                // delete old images (DB + files)
                foreach ($listing->images as $oldImage) {
                    $oldPath = public_path($oldImage->path);

                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }

                $listing->images()->delete();

                $imagesPayload = [];

                foreach ($request->file('images') as $imageFile) {

                    $fileName = 'img_'.Str::random(10).'.'.$imageFile->getClientOriginalExtension();
                    $destinationPath = public_path('listings');

                    // create directory if not exists
                    if (! File::exists($destinationPath)) {
                        File::makeDirectory($destinationPath, 0755, true);
                    }

                    // move file to public/listings
                    $imageFile->move($destinationPath, $fileName);

                    $path = 'listings/'.$fileName;

                    $img = ListingImage::create([
                        'listing_id' => $listing->id,
                        'image_code' => 'img_'.Str::random(5),
                        'path' => $path,
                    ]);

                    $imagesPayload[] = [
                        'id' => $img->image_code,
                        'url' => '/'.$path,
                    ];
                }
            } else {
                // keep existing images
                $imagesPayload = $listing->images->map(function ($img) {
                    return [
                        'id' => $img->image_code,
                        'url' => '/'.$img->path,
                    ];
                })->all();
            }

            $data = [
                'listing_id' => $listing->listing_code,
                'title' => $listing->title,
                'price' => (float) $listing->price,
                'currency' => $listing->currency,
                'category' => [
                    'id' => $listing->category_id,
                    'name' => $listing->category_name,
                ],
                'condition' => [
                    'id' => $listing->condition_id,
                    'name' => $listing->condition_name,
                ],
                'location' => [
                    'name' => $listing->location_name,
                    'latitude' => $listing->latitude,
                    'longitude' => $listing->longitude,
                ],
                'images' => $imagesPayload,
                'status' => $listing->status,
                'created_at' => $listing->created_at?->toIso8601String(),
            ];

            return ApiResponse::success('Listing edited successfully', ['data' => $data]);
        });
    }

    public function deleteListing(Request $request)
    {
        $user = auth('api')->user();

        $validated = $request->validate([
            'listing_id' => 'required|string',
        ]);

        $listing = Listing::where('listing_code', $validated['listing_id'])->first();

        if (! $listing) {
            return response()->json([
                'status' => false,
                'message' => 'Listing not found',
            ], 404);
        }

        if ($listing->user_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

         // delete old images (DB + files)
        foreach ($listing->images as $oldImage) {
            $oldPath = public_path($oldImage->path);

            if (File::exists($oldPath)) {
                File::delete($oldPath);
            }
        }
        // If using SoftDeletes on Listing, this will soft delete; otherwise, hard delete.[web:10][web:16]
        $listing->delete();

        /*return response()->json([
            'status' => true,
            'message' => 'Listing deleted successfully',
        ]);*/
        return ApiResponse::success('Listing deleted successfully');
    }

    /**
     * GET /api/my_listings
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function myListings(Request $request)
    {
        $user = auth('api')->user();
        $page = (int) $request->query('page', 1);
        $limit = (int) $request->query('limit', 10);
        $limit = $limit > 0 ? min($limit, 100) : 10;

        $query = Listing::with(['owner', 'images'])
            ->where('user_id', $user->id);

        $paginator = $query
            ->orderByDesc('created_at')
            ->paginate($limit, ['*'], 'page', $page); // filtered pagination.[web:35][web:32]

        $listings = $paginator->getCollection()->map(function (Listing $listing) use ($user) {
            $rating = round($user->receivedReviews()->avg('overall_rating') ?? 0, 1);
            $totalReview = $user->receivedReviews()->count();

            return [
                'listing_id' => $listing->listing_code,
                'title' => $listing->title,
                'price' => (float) $listing->price,
                'currency' => $listing->currency,
                'location' => $listing->location_name,
                'images' => $listing->images->pluck('path')->values()->all(),
                'category' => [
                    'id' => $listing->category_id,
                    'name' => $listing->category_name,
                ],
                'condition' => [
                    'id' => $listing->condition_id,
                    'name' => $listing->condition_name,
                ],
                'seller' => [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'rating' => $rating,
                    'total_review' => $totalReview,
                ],
                'description' => $listing->description,
                'is_featured' => (bool) $listing->is_featured,
                'created_at' => $listing->created_at?->toIso8601String(),
            ];
        })->values()->all();

        $data = [
            'listings' => $listings,
            'pagination' => [
                'page' => $paginator->currentPage(),
                'limit' => $paginator->perPage(),
                'total_pages' => $paginator->lastPage(),
                'total_records' => $paginator->total(),
            ],
        ];

        return response()->json([
            'status' => true,
            'message' => 'Listings fetched successfully',
            'data' => $data,
        ]);
    }

    /**
     * GET /api/listing_details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $request->validate([
            'listing_id' => 'required|string',
        ]);

        $listing = Listing::with(['owner', 'images'])->where('listing_code', $request->query('listing_id'))->first();

        if (! $listing) {
            return response()->json([
                'status' => false,
                'message' => 'Listing not found',
            ], 404);
        }

        $owner = $listing->owner;
        $rating = 0.0;

        if ($owner) {
            $rating = round($owner->receivedReviews()->avg('overall_rating') ?? 0, 1); // reuse seller rating logic.[web:30][web:36]
        }

        $data = [
            'listing_id' => $listing->listing_code,
            'title' => $listing->title,
            'price' => (float) $listing->price,
            'currency' => $listing->currency,
            'category' => [
                'id' => $listing->category_id,
                'name' => $listing->category_name,
            ],
            'condition' => [
                'id' => $listing->condition_id,
                'name' => $listing->condition_name,
            ],
            'location' => [
                'name' => $listing->location_name,
                'latitude' => $listing->latitude,
                'longitude' => $listing->longitude,
            ],
            'description' => $listing->description,
            'images' => $listing->images->pluck('path')->values()->all(),
            'seller' => $owner ? [
                'user_id' => $owner->id,
                'name' => $owner->name,
                'rating' => $rating,
            ] : null,
            'status' => $listing->status,
            'created_at' => $listing->created_at?->toIso8601String(),
        ];

        return response()->json([
            'status' => true,
            'message' => 'Listing details fetched successfully',
            'data' => $data,
        ]);
    }
}
