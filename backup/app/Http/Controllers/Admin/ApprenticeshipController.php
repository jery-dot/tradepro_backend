<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Opportunity; // 🔥 Corrigé (Modèle réel aligné sur vos fichiers)
use App\Models\User;
use App\Models\JobPost; // 🔥 Corrigé (Évite le conflit système)
use Illuminate\Http\Request;

class ApprenticeshipController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'opportunities');

        // ── Requête sur les Opportunités (Apprenticeship Offers) ─────────────
        $oppQuery = Opportunity::with('user')->latest();

        // Recherche par titre ou par nom de l'éditeur / compagnie
        if ($request->filled('search') && $tab === 'opportunities') {
            $s = $request->search;
            $oppQuery->where(function ($q) use ($s) {
                $q->where('title', 'like', "%$s%")
                  ->orWhere('city', 'like', "%$s%")
                  ->orWhereHas('user', function($userQuery) use ($s) {
                      $userQuery->where('name', 'like', "%$s%");
                  });
            });
        }

        // Filtre par compétences / métiers (recherche dans le tableau JSON casts)
        if ($request->filled('trade') && $request->trade !== 'all') {
            $oppQuery->whereJsonContains('skills_needed', $request->trade);
        }

        // Filtre par localisation géographique réelle (colonne 'city')
        if ($request->filled('location') && $request->location !== 'all') {
            $oppQuery->where('city', $request->location);
        }

        // ── Requête sur les Candidats (Apprentices Profiles) ──────────────────
        // Note: Si vous n'avez pas de modèle pivot d'application dédié, on filtre les utilisateurs de type 'Apprentice' (user_type = 3)
        $appQuery = User::where('user_type', 3)->with(['apprentice', 'apprenticeProfile'])->latest();
        
        if ($request->filled('search') && $tab === 'applicants') {
            $s = $request->search;
            $appQuery->where(function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('email', 'like', "%$s%")
                  ->orWhere('city', 'like', "%$s%");
            });
        }

        // Pagination et compteurs
        $opportunities     = $oppQuery->paginate(15, ['*'], 'opp_page')->withQueryString();
        $applicants        = $appQuery->paginate(15, ['*'], 'app_page')->withQueryString();
        
        $opportunityCount  = Opportunity::count();
        $applicantCount    = User::where('user_type', 3)->count();
        
        // Extraction des villes distinctes pour les filtres de la vue admin
        $locations         = Opportunity::distinct()->whereNotNull('city')->pluck('city')->sort()->values();
        
        // Statistiques globales pour la mise en page de l'administration
        $userCount         = User::count();
        $jobCount          = JobPost::where('status', 'open')->count();
        $apprenticeCount   = $opportunityCount; // Total d'apprentissages disponibles

        // dd($applicants);
        return view('admin.apprenticeships', compact(
            'opportunities', 'applicants', 'opportunityCount', 'applicantCount',
            'locations', 'userCount', 'jobCount', 'apprenticeCount', 'tab'
        ));
    }

    /**
     * Voir les détails d'une opportunité d'apprentissage
     */
    public function show(Opportunity $opportunity)
    {
        // Chargement de l'éditeur (Créateur du poste)
        $opportunity->load('user');
        
        $userCount = User::count();
        $jobCount  = JobPost::where('status', 'open')->count();
        
        return view('admin.apprenticeships-show', compact('opportunity', 'userCount', 'jobCount'));
    }

    /**
     * Voir le profil complet d'un apprenti candidat
     */
    public function showApplicant(User $user)
    {
        // Sécurité : On s'assure qu'il s'agit bien d'un profil Apprenti (user_type = 3)
        if ((int)$user->user_type !== 3) {
            abort(404, "This user is not registered as an apprentice.");
        }

        // Chargement de ses profils détaillés
        $user->load(['apprentice', 'apprenticeProfile']);
        
        $userCount = User::count();
        $jobCount  = JobPost::where('status', 'open')->count();
        
        return view('admin.apprenticeship-applicant', compact('user', 'userCount', 'jobCount'));
    }

    /**
     * Supprimer une opportunité d'apprentissage (SoftDeletes géré par le modèle)
     */
    public function destroy(Opportunity $opportunity)
    {
        $opportunity->delete();
        return redirect()->route('admin.apprenticeships')
            ->with('status', 'Apprenticeship opportunity deleted successfully.');
    }

    /**
     * Clôturer manuellement une opportunité (si vous gérez un flag ou un statut)
     * Note : Votre modèle ne possède pas de colonne 'status' nativement dans le fillable, 
     * mais si vous l'ajoutez ou l'avez configuré, voici la méthode propre :
     */
    public function close(Opportunity $opportunity)
    {
        // Si vous utilisez un système de statut ou si vous souhaitez simplement la désactiver
        // $opportunity->update(['status' => 'closed']);
        
        // Alternative si vous préférez la masquer en utilisant le SoftDelete natif de votre modèle :
        $opportunity->delete(); 
        
        return back()->with('status', 'Opportunity closed.');
    }
}