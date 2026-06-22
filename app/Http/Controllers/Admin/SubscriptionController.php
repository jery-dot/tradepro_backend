<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserSubscription; // 🔥 Corrigé (Modèle réel)
use App\Models\Plan;             // 🔥 Ajouté pour extraire les plans dynamiquement
use App\Models\User;
use App\Models\JobPost;          // 🔥 Aligné sur votre modèle réel
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        // Chargement de l'utilisateur et du plan associé à l'abonnement
        $query = UserSubscription::with(['user', 'plan'])->latest();

        // 1. Filtre par plan (via jointure relationnelle sur la table plans)
        if ($request->filled('plan') && $request->plan !== 'all') {
            $query->whereHas('plan', function ($q) use ($request) {
                $q->where('name', 'like', ucfirst($request->plan) . '%');
            });
        }

        $subscriptions = $query->paginate(15)->withQueryString();

        // 2. Définition de la logique SQL exacte pour un abonnement "Actif" (Évite l'erreur Unknown column status)
        $activeSubscriptionFilter = function ($q) {
            $q->whereIn('user_subscriptions.stripe_status', ['active', 'trialing', 'cancelling'])
              ->where(function ($subQuery) {
                  $subQuery->whereNull('user_subscriptions.ends_at')
                           ->orWhere('user_subscriptions.ends_at', '>', now());
              });
        };

        // 3. Calcul dynamique et performant des statistiques par Plan
        $plansStats = [];
        $availablePlans = ['Labourer', 'Contractor', 'Apprentice'];

        foreach ($availablePlans as $planName) {
            $planModel = Plan::where('name', 'like', $planName . '%')->first();
            
            if ($planModel) {
                // Nombre d'abonnés actifs réels pour ce plan
                $count = $planModel->subscriptions()->where($activeSubscriptionFilter)->count();
                
                // Calcul du MRR (Prix du plan * Nombre d'abonnés actifs)
                $mrr = $count * (float) $planModel->price;
            } else {
                $count = 0;
                $mrr = 0;
            }

            $plansStats[strtolower($planName)] = [
                'count' => $count,
                'mrr'   => $mrr,
            ];
        }

        // 4. Statistiques globales pour la mise en page
        $userCount = User::count();
        $jobCount  = JobPost::where('status', 'open')->count();

        return view('admin.subscriptions', [
            'subscriptions' => $subscriptions,
            'plans'         => $plansStats, // Conservé sous la variable 'plans' pour votre vue
            'userCount'     => $userCount,
            'jobCount'      => $jobCount
        ]);
    }

    /**
     * Voir les détails d'un abonnement Stripe spécifique
     */
    public function show(UserSubscription $subscription)
    {
        $subscription->load(['user', 'plan']);
        
        $userCount = User::count();
        $jobCount  = JobPost::where('status', 'open')->count();
        
        return view('admin.subscription-show', compact('subscription', 'userCount', 'jobCount'));
    }

    /**
     * Annuler / Clôturer un abonnement côté base de données
     */
    public function cancel(UserSubscription $subscription)
    {
        // Aligné sur les colonnes réelles de votre intégration Stripe (ends_at, stripe_status)
        $subscription->update([
            'stripe_status' => 'canceled',
            'ends_at'       => now(),
        ]);
        
        return back()->with('status', 'Subscription has been marked as cancelled.');
    }
}