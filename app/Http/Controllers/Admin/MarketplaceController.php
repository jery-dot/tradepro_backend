<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\User;
use App\Models\JobPost; // 🔥 Alignement sur votre modèle réel
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        // 1. Utilisation de la relation réelle 'owner'
        $query = Listing::with('owner')->latest();

        // 2. Filtre de catégorie aligné sur 'category_name'
        $category = $request->get('category', 'all');
        if ($category && $category !== 'all') {
            $query->where('category_name', $category);
        }

        // 3. Filtre de condition aligné sur 'condition_name'
        if ($request->filled('condition') && $request->condition !== 'all') {
            $query->where('condition_name', $request->condition);
        }

        // 4. Recherche textuelle (Titre ou Nom du propriétaire via jointure)
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%$s%")
                  ->orWhereHas('owner', function ($userQuery) use ($s) {
                      $userQuery->where('name', 'like', "%$s%");
                  });
            });
        }

        $listings   = $query->paginate(15)->withQueryString();
        $totalCount = Listing::count();

        // 5. Compteurs par catégories basés sur 'category_name'
        $categoryCounts = [
            'all'       => $totalCount,
            'tools'     => Listing::where('category_name', 'tools')->count(),
            'equipment' => Listing::where('category_name', 'equipment')->count(),
            'materials' => Listing::where('category_name', 'materials')->count(),
            'companies' => Listing::where('category_name', 'companies')->count(),
        ];

        $userCount = User::count();
        $jobCount  = JobPost::where('status', 'pending')->count();
        $apprenticeCount = \App\Models\Opportunity::count();

        return view('admin.marketplace', compact('listings', 'totalCount', 'categoryCounts', 'userCount', 'jobCount', 'apprenticeCount'));
    }

    public function show(Listing $listing)
    {
        // Utilisation de la relation réelle 'owner'
        $listing->load('owner');
        
        $userCount = User::count();
        $jobCount  = JobPost::where('status', 'pending')->count();
        $apprenticeCount = \App\Models\Opportunity::count();
        
        return view('admin.marketplace-show', compact('listing', 'userCount', 'jobCount', 'apprenticeCount'));
    }

    public function destroy(Listing $listing)
    {
        $listing->delete();
        return redirect()->route('admin.marketplace')->with('status', 'Listing removed.');
    }

    // 6. Gestion des statuts via la colonne 'status' (et non via des booléens inexistants)
    public function deactivate(Listing $listing)
    {
        $listing->update(['status' => 'inactive']);
        return back()->with('status', 'Listing deactivated.');
    }

    public function activate(Listing $listing)
    {
        $listing->update(['status' => 'active']);
        return back()->with('status', 'Listing activated.');
    }

    public function flag(Listing $listing)
    {
        $listing->update(['status' => 'flagged']);
        return back()->with('status', 'Listing flagged for review.');
    }

    public function export()
    {
        $headers  = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=marketplace_' . now()->format('Y-m-d') . '.csv'
        ];
        
        $listings = Listing::with('owner')->get();
        
        $callback = function () use ($listings) {
            $f = fopen('php://output', 'w');
            
            // En-têtes alignés sur vos colonnes réelles
            fputcsv($f, ['ID', 'Listing Code', 'Title', 'Category', 'Seller (Owner)', 'Price', 'Currency', 'Condition', 'Location Name', 'Listed At', 'Status']);
            
            foreach ($listings as $l) {
                $sellerName = $l->owner ? $l->owner->name : 'N/A';
                $priceFormatted = ($l->currency ?? '$') . number_format($l->price, 2);
                
                fputcsv($f, [
                    $l->id,
                    $l->listing_code ?? 'N/A',
                    $l->title,
                    $l->category_name ?? 'N/A',
                    $sellerName,
                    $priceFormatted,
                    $l->currency ?? 'USD',
                    $l->condition_name ?? 'N/A',
                    $l->location_name ?? 'N/A',
                    $l->created_at ? $l->created_at->format('d M Y') : 'N/A',
                    $l->status ?? 'active'
                ]);
            }
            fclose($f);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}