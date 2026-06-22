<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\JobPost; // 🔥 Corrigé (Évite le conflit avec la table système)
use App\Enums\UserType;  // 🔥 Ajouté pour exploiter votre Enum natif
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->latest();

        // 1. Type tab filter (Gestion via l'Enum UserType)
       if ($request->filled('type') && $request->type !== 'all') {
            $typeValue = match ($request->type) {
                'contractor'    => \App\Enums\UserType::CONTRACTOR->value,   // 0
                'subcontractor' => \App\Enums\UserType::SUBCONTRACTOR->value,// 1
                'labour', 'laborer' => \App\Enums\UserType::LABORER->value,  // 2 (Sécurisé pour les deux orthographes)
                'apprentice'    => \App\Enums\UserType::APPRENTICE->value,   // 3
                default         => null,
            };

            if ($typeValue !== null) {
                $query->where('user_type', $typeValue);
            }
        }

        // 2. Search (Aligné sur la colonne unique 'name')
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('email', 'like', "%$s%")
                  ->orWhere('city', 'like', "%$s%");
            });
        }

        // 3. Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // 4. Insured filter (Jointure dynamique sur la table laborers.has_insurance)
        if ($request->filled('insured') && $request->insured !== 'all') {
            $isInsured = $request->insured === 'yes';
            $query->whereHas('laborer', function ($q) use ($isInsured) {
                $q->where('has_insurance', $isInsured);
            });
        }

        // Per-type counts for tabs (Utilisation sécurisée des valeurs d'Enum)
        $counts = [
            'all'           => User::count(),
            'contractor'    => User::where('user_type', 0)->count(),
            'subcontractor' => User::where('user_type', 1)->count(),
            'labour'        => User::where('user_type', 2)->count(),
            'apprentice'    => User::where('user_type', 3)->count(),
        ];

        $users      = $query->paginate(15)->withQueryString();
        $totalCount = $counts['all'];

        // Sidebar counts
        $userCount = $totalCount;
        $jobCount  = JobPost::where('status', 'open')->count();

        // dd($users);

        return view('admin.users', compact('users', 'counts', 'totalCount', 'userCount', 'jobCount'));
    }

    public function show(User $user)
    {
        // Chargement des offres d'emploi postées par cet utilisateur (owner)
        $user->load(['jobPosts' => fn($q) => $q->latest()->take(5)]);
        
        // Eager-loading des profils spécifiques pour affichage riche dans la vue admin
        $user->load(['laborer.specialization', 'contractor', 'apprentice']);

        $userCount = User::count();
        $jobCount  = JobPost::where('status', 'open')->count();
        
        return view('admin.user-show', compact('user', 'userCount', 'jobCount'));
    }

    public function destroy(User $user)
    {
        $userName = $user->name;
        $user->delete();
        
        return redirect()->route('admin.users.index') // Aligné sur la nomenclature classique de vos routes
            ->with('status', $userName . ' has been deleted.');
    }

    public function suspend(User $user)
    {
        $user->update(['status' => 'suspended']);
        return redirect()->back()
            ->with('status', $user->name . ' has been suspended.');
    }

    public function deactivate(User $user)
    {
        $user->update(['status' => 'inactive']);
        return redirect()->back()
            ->with('status', $user->name . ' has been set to inactive.');
    }

    public function reactivate(User $user)
    {
        $user->update(['status' => 1]);
        return redirect()->back()
            ->with('status', $user->name . ' has been reactivated.');
    }

    public function export(Request $request)
    {
        $headers = [
            'Content-Type' => 'text/csv', 
            'Content-Disposition' => 'attachment; filename=users_' . now()->format('Y-m-d') . '.csv'
        ];
        
        // Eager-load des relations pour éviter les requêtes N+1 lors de l'export lourd
        $users = User::with(['laborer.specialization', 'apprentice'])->get();
        
        $callback = function () use ($users) {
            $f = fopen('php://output', 'w');
            
            // En-tête du fichier CSV
            fputcsv($f, ['ID', 'Name', 'Email', 'Type', 'City', 'Specialization/Trade', 'Experience Level', 'Insured', 'Background Checked', 'Status', 'Joined']);
            
            foreach ($users as $u) {
                // 1. Extraction textuelle du type de l'Enum
                $typeLabel = match($u->user_type instanceof UserType ? $u->user_type->value : (int)$u->user_type) {
                    0 => 'Contractor',
                    1 => 'Sub-contractor',
                    2 => 'Laborer',
                    3 => 'Apprentice',
                    default => 'Unknown',
                };

                // 2. Extraction dynamique des données selon le profil
                $specialization = 'N/A';
                $experience = 'N/A';
                $insured = 'N/A';
                $background = 'N/A';

                if ($u->laborer) {
                    $specialization = $u->laborer->specialization ? $u->laborer->specialization->name : ($u->laborer->custom_specialization ?? 'General');
                    $experience     = $u->laborer->experience_level;
                    $insured        = $u->laborer->has_insurance ? 'Yes' : 'No';
                    $background     = $u->laborer->background_check_completed ? 'Yes' : 'No';
                } elseif ($u->apprentice) {
                    $specialization = $u->apprentice->tradeInterest ? $u->apprentice->tradeInterest->name : 'Apprentice';
                    $experience     = 'Year ' . $u->apprentice->current_program_year;
                }

                fputcsv($f, [
                    $u->id, 
                    $u->name, 
                    $u->email,
                    $typeLabel, 
                    $u->city ?? 'N/A', 
                    $specialization,
                    $experience, 
                    $insured, 
                    $background, 
                    $u->status ?? 'active',
                    $u->created_at ? $u->created_at->format('d M Y') : 'N/A',
                ]);
            }
            fclose($f);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}