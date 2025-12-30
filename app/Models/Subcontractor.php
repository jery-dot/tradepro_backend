<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subcontractor extends Model
{

    protected $fillable = [
        'user_id',
        'insurance_file_path',
        'profile_completion',
    ];

    protected function casts(): array
    {
        return [
            'profile_completion'=> 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
