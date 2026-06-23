<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = ['name', 'stripe_price_id', 'price', 'features', 'trial_days'];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'trial_days' => 'integer',
    ];

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }
}

