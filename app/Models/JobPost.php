<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JobPost extends Model
{
    // Explicit table name to avoid conflict with any existing "jobs" table
    protected $table = 'job_posts';

    protected $fillable = [
        'job_code',
        'user_id',
        'specialization_id',
        'start_date',
        'duration_value',
        'duration_unit',
        'pay_range',
        'location_lat',
        'location_lng',
        'job_description',
        'is_featured',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'pay_range' => 'float',
            'location_lat' => 'float',
            'location_lng' => 'float',
            'is_featured' => 'boolean',
        ];
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'job_post_skill');
    }
}
