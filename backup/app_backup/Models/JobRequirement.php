<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JobRequirement extends Model
{
   protected $fillable = [
        'name',
    ];

    public function contractors(): BelongsToMany
    {
        return $this->belongsToMany(Contractor::class, 'contractor_job_requirement');
    }
}
