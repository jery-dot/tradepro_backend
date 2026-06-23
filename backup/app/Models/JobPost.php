<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JobPost extends Model
{
    // Explicit table name to avoid conflict with any existing "jobs" table
    protected $table = 'job_posts';

    protected $fillable = [
        'title',
        'company_name',
        'job_code',
        'user_id',
        'specialization_id',
        'start_date',
        'duration_value',
        'duration_unit',
        'pay_rate_amount',
        'pay_rate_currency',
        'pay_rate_type',
        'location_lat',
        'location_lng',
        'city',
        'state',
        'country',
        'job_description',
        'is_featured',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date'    => 'date',
            'pay_rate_amount' => 'float',
            'location_lat'  => 'float',
            'location_lng'  => 'float',
            'is_featured'   => 'boolean',
        ];
    }

    /**
     * Get the nicely formatted location string.
     */
    public function getFormattedLocationAttribute(): string
    {
        // Filter out null or empty values, keeping only valid location fragments
        $parts = array_filter([
            $this->city,
            $this->state,
            $this->country
        ], fn($value) => !empty(trim($value)));

        // Return the combined string, or a fallback if completely empty
        return !empty($parts) ? implode(', ', $parts) : 'Remote / Global';
    }


    /**
     * Get the nicely formatted duration string.
     */
    public function getFormattedDurationAttribute(): string
    {
        if (empty($this->duration_value) || empty($this->duration_unit)) {
            return 'Not Specified';
        }

        // Automatically pluralizes the unit if value > 1 (e.g., "month" becomes "months")
        $unit = $this->duration_value > 1 
            ? Str::plural(strtolower($this->duration_unit)) 
            : Str::singular(strtolower($this->duration_unit));

        return "{$this->duration_value} {$unit}";
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'job_post_skill');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function specialization(){
        return $this->belongsTo(Specialization::class, 'specialization_id');
    }

}
