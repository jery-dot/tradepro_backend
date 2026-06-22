<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
