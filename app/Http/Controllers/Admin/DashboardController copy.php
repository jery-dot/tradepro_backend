<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\JobPost; // 🔥 Corrigé (évite les conflits avec la table système 'jobs')
use App\Models\Opportunity; // 🔥 Corrigé (Aligné sur votre modèle)
use App\Models\Listing;
use App\Models\Plan; // 🔥 Ajouté pour l'extraction financière des abonnements
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Stats ──────────────────────────────────────────────────────────
        $stats = [
            'total_users'         => User::count(),
            'new_users_week'      => User::where('created_at', '>=', now()->startOfWeek())->count(),
            'active_jobs'         => JobPost::where('status', 'open')->count(),
            'new_jobs_today'      => JobPost::whereDate('created_at', today())->count(),
            'apprenticeships'     => Opportunity::count(), // Géré par Opportunity
            'new_apprenticeships' => Opportunity::where('created_at', '>=', now()->subDays(7))->count(),
            'marketplace_listings'=> Listing::where('status', 'active')->count(), // Aligné sur la colonne 'status'
            'removed_listings'    => Listing::whereDate('updated_at', today())->where('status', 'inactive')->count(),
            
            // Calcul dynamique réel du MRR en joignant les tables via les relations Éloquent existantes
            'mrr'                 => \DB::table('user_subscriptions')
                                        ->join('plans', 'user_subscriptions.plan_id', '=', 'plans.id')
                                        ->where('user_subscriptions.status', 'active')
                                        ->sum('plans.price'),
            'mrr_growth'          => 12, // Remplacer par votre formule de comparaison M-1 si nécessaire
        ];

        // ── User type distribution (0=contractor, 1=subcontractor, 2=laborer, 3=apprentice) ─────────────────
        $total = max($stats['total_users'], 1);
        $userTypes = [
            'contractor_pct'    => round(User::where('user_type', 0)->count() / $total * 100),
            'subcontractor_pct' => round(User::where('user_type', 1)->count() / $total * 100),
            'labour_pct'        => round(User::where('user_type', 2)->count() / $total * 100),
            'apprentice_pct'    => round(User::where('user_type', 3)->count() / $total * 100),
        ];

        // ── Weekly registrations (Mon–Sun this week) ───────────────────────
        $weeklyRegistrations = [];
        for ($i = 0; $i < 7; $i++) {
            $day = Carbon::now()->startOfWeek()->addDays($i);
            $weeklyRegistrations[] = User::whereDate('created_at', $day)->count();
        }

        // ── Recent activity feed ───────────────────────────────────────────
        $recentActivity = $this->buildActivityFeed();

        // ── MRR breakdown basé sur vos Plans enregistrés ──────────────────
        // On récupère le volume d'abonnés actifs par clé Stripe ou par nom de plan
        $contractorPlan = Plan::where('name', 'like', '%Contractor%')->first();
        $laborerPlan    = Plan::where('name', 'like', '%Laborer%')->orWhere('name', 'like', '%Labourer%')->first();
        $apprenticePlan = Plan::where('name', 'like', '%Apprentice%')->first();

        $contractorCount = $contractorPlan ? $contractorPlan->subscriptions()->where('status', 'active')->count() : 0;
        $labourerCount   = $laborerPlan ? $laborerPlan->subscriptions()->where('status', 'active')->count() : 0;
        $apprenticeCount = $apprenticePlan ? $apprenticePlan->subscriptions()->where('status', 'active')->count() : 0;
        
        $totalSubs        = max($labourerCount + $contractorCount + $apprenticeCount, 1);

        $mrrBreakdown = [
            [
                'name'  => 'Labourers ($' . ($laborerPlan->price ?? '19.99') . '/mo)', 
                'count' => $labourerCount, 
                'color' => '#1B3D6F', 
                'pct'   => round($labourerCount / $totalSubs * 100)
            ],
            [
                'name'  => 'Contractors ($' . ($contractorPlan->price ?? '59.99') . '/mo)', 
                'count' => $contractorCount, 
                'color' => '#F5874F', 
                'pct'   => round($contractorCount / $totalSubs * 100)
            ],
            [
                'name'  => 'Apprentices ($' . ($apprenticePlan->price ?? '9.99') . '/mo)', 
                'count' => $apprenticeCount, 
                'color' => '#8E44AD', 
                'pct'   => round($apprenticeCount / $totalSubs * 100)
            ],
        ];

        // ── Sidebar counts ─────────────────────────────────────────────────
        $userCount        = $stats['total_users'];
        $jobCount         = $stats['active_jobs'];

        return view('admin.dashboard', compact(
            'stats', 'userTypes', 'weeklyRegistrations',
            'recentActivity', 'mrrBreakdown',
            'userCount', 'jobCount'
        ));
    }

    private function buildActivityFeed(): array
    {
        $feed = [];

        // Recent user registrations
        User::latest()->take(3)->get()->each(function ($user) use (&$feed) {
            // Mappage des entiers de votre table users vers des labels textuels propres
            $typeLabel = [
                0 => 'Contractor',
                1 => 'Sub-contractor',
                2 => 'Laborer',
                3 => 'Apprentice'
            ][$user->user_type] ?? 'Unknown';

            $colors = [
                0 => '#1B3D6F', // Contractor
                1 => '#F5874F', // Sub-contractor
                2 => '#27AE60', // Laborer
                3 => '#8E44AD'  // Apprentice
            ];

            $feed[] = [
                'text'  => '<strong>' . e($user->name) . '</strong> registered as ' . $typeLabel, // Aligné sur la colonne 'name'
                'time'  => $user->created_at->diffForHumans(),
                'color' => $colors[$user->user_type] ?? '#64748B',
            ];
        });

        // Recent jobs
        JobPost::latest()->with('owner')->take(2)->get()->each(function ($job) use (&$feed) {
            $feed[] = [
                'text'  => '<strong>' . e($job->title) . '</strong> job posted ($' . number_format($job->pay_rate_amount, 2) . '/' . e($job->duration_unit ?? 'hr') . ')',
                'time'  => $job->created_at->diffForHumans(),
                'color' => '#F5874F',
            ];
        });

        // Recent marketplace listings
        Listing::latest()->take(1)->get()->each(function ($listing) use (&$feed) {
            $feed[] = [
                'text'  => '<strong>' . e($listing->title) . '</strong> listed in marketplace (' . e($listing->currency) . ' ' . number_format($listing->price, 2) . ')',
                'time'  => $listing->created_at->diffForHumans(),
                'color' => '#17A589',
            ];
        });

        // Tri à plat par date (déjà ordonné par les requêtes latest())
        return array_slice($feed, 0, 6);
    }

    public function export()
    {
        // Votre logique d'exportation de statistiques de plateforme
        return back()->with('status', 'Export feature coming soon.');
    }

    public function search(Request $request)
    {
        $q = $request->get('q');
        return redirect()->route('admin.users.index', ['search' => $q]); // Adapté à votre alias de route 'admin.users.index'
    }

    public function activity()
    {
        return view('admin.activity');
    }
}