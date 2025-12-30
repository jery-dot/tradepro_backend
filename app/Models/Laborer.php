<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Laborer extends Model
{

    protected $fillable = [
        'user_id',
        'specialization_id',
        'custom_specialization',
        'experience_level',
        'age',
        'gender',
        'has_insurance',
        'background_check_completed',
        'looking_for_apprenticeship',
        'trade_school_name',
        'trade_school_program_year',
        'profile_completion',
    ];

    protected function casts(): array
    {
        return [
            'has_insurance'              => 'boolean',
            'background_check_completed' => 'boolean',
            'looking_for_apprenticeship' => 'boolean',
            'profile_completion'         => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }
}
