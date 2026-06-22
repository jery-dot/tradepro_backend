<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Apprentice extends Model
{
    protected $fillable = [
        'user_id',
        'trade_interest_id',
        'trade_school_name',
        'current_program_year',
        'experience_level',
        'profile_completion',
    ];

    protected function casts(): array
    {
        return [
            'profile_completion' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tradeInterest(): BelongsTo
    {
        return $this->belongsTo(TradeInterest::class);
    }
}
