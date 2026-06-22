<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\User;
use App\Models\JobPost; // 🔥 Alignement sur votre modèle réel
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        // Chargement immédiat des auteurs et des destinataires de l'avis
        $query = Review::with(['reviewer', 'reviewee', 'jobPost'])->latest();

        // 1. Filtrage par note (overall_rating)
        if ($request->filled('rating') && $request->rating !== 'all') {
            if ($request->rating === 'low') {
                $query->whereBetween('overall_rating', [1, 2]);
            } else {
                $query->where('overall_rating', (int) $request->rating);
            }
        }

        // 2. Filtrage par type d'avis (Aligné sur 'review_type' défini dans votre fillable)
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('review_type', $request->type);
        }

        $reviews = $query->paginate(20)->withQueryString();

        // 3. Calcul des statistiques globales
        $totalReviews = Review::count();
        
        $stats = [
            'avg_rating'      => Review::avg('overall_rating') ?? 0,
            'total_reviews'   => $totalReviews,
            // 'is_flagged' n'étant pas dans votre modèle, on le laisse à 0 pour éviter le crash SQL
            'flagged_reviews' => 0, 
            'recommend_pct'   => $totalReviews > 0
                ? round(Review::where('recommendation', 'yes')->count() / $totalReviews * 100)
                : 0,
        ];

        // 4. Statistiques pour la barre latérale globale de l'admin
        $userCount = User::count();
        $jobCount  = JobPost::where('status', 'open')->count();

        return view('admin.reviews', compact('reviews', 'stats', 'userCount', 'jobCount'));
    }

    /**
     * Afficher les détails d'un avis spécifique
     */
    public function show(Review $review)
    {
        $review->load(['reviewer', 'reviewee', 'jobPost']);
        
        $userCount = User::count();
        $jobCount  = JobPost::where('status', 'open')->count();
        
        return view('admin.reviews-show', compact('review', 'userCount', 'jobCount'));
    }

    /**
     * Supprimer un avis (Modération de l'administrateur)
     */
    public function destroy(Review $review)
    {
        $review->delete();
        
        // Redirection vers la route index de l'administration
        return redirect()->route('admin.reviews')
            ->with('status', 'The review has been permanently removed.');
    }
}