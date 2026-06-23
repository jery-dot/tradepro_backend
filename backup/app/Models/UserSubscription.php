<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;   

class UserSubscription extends Model
{
    protected $fillable = [
        'user_id', 'plan_id', 'stripe_subscription_id', 'stripe_status',
        'stripe_price_id', 'quantity', 'trial_ends_at', 'ends_at'
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * 🔥 ACCESSEUR : Détermine dynamiquement le 'status'
     * * Règles de calcul :
     * 1. Si Stripe dit que c'est annulé/incomplet, le statut est 'inactive'.
     * 2. Si la date 'ends_at' existe et qu'elle est dépassée (dans le passé), l'abonnement a expiré -> 'inactive'.
     * 3. Sinon, si Stripe est au statut 'active', 'trialing' ou 'cancelling' (en cours de résiliation mais valide jusqu'à la fin du mois), l'abonnement est 'active'.
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: function () {
                // 1. Vérification du statut Stripe natif
                if (in_array($this->stripe_status, ['canceled', 'incomplete', 'incomplete_expired'])) {
                    return 'inactive';
                }

                // 2. Vérification de la date de fin (ends_at)
                if ($this->ends_at && $this->ends_at->isPast()) {
                    return 'inactive';
                }

                // 3. Si Stripe est valide et que la date n'est pas dépassée
                if (in_array($this->stripe_status, ['active', 'trialing', 'cancelling'])) {
                    return 'active';
                }

                return 'inactive';
            }
        );
    }
    
    public function user() 
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function isValid()
    {
        // A subscription is valid if it's active, trialing,
        // or 'cancelling' (which means they paid for the month but turned off auto-renew).
        return in_array($this->stripe_status, ['active', 'trialing', 'cancelling']);
    }
}
