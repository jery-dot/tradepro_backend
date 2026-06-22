<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Contractor extends Model
{
     protected $fillable = [
        'user_id',
        'file_path',
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

    public function jobRequirements(): BelongsToMany
    {
        return $this->belongsToMany(JobRequirement::class, 'contractor_job_requirement');
    }
}
