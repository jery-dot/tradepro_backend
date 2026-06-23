<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\User;
use App\Models\Job;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::latest();

        if ($request->filled('rating') && $request->rating !== 'all') {
            if ($request->rating === 'low') {
                $query->whereBetween('overall_rating',[1,2]);
            } else {
                $query->where('overall_rating', $request->rating);
            }
        }
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('reviewed_type', $request->type);
        }

        $reviews = $query->paginate(20)->withQueryString();

        $stats = [
            'avg_rating'     => Review::avg('overall_rating') ?? 0,
            'total_reviews'  => Review::count(),
            'flagged_reviews'=> Review::where('is_flagged', true)->count(),
            'recommend_pct'  => Review::count() > 0
                ? round(Review::where('would_recommend',true)->count() / Review::count() * 100)
                : 0,
        ];

        $userCount = User::count();
        $jobCount  = Job::where('status','open')->count();

        return view('admin.reviews', compact('reviews','stats','userCount','jobCount'));
    }

    public function show(Review $review)
    {
        return view('admin.reviews-show', compact('review'));
    }

    public function destroy(Review $review)
    {
        $review->delete();
        return redirect()->route('admin.reviews')->with('status','Review removed.');
    }
}
