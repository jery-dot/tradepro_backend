<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Fillable Fields
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'review_code',
        'job_post_id',
        'reviewer_id',
        'reviewee_id',
        'review_type', // 🔥 added
        'overall_rating',
        'recommendation',
        'communication_rating',
        'job_quality_rating',
        'professionalism_rating',
        'job_complete_satisfaction',
        'comment',
        'average_rating',
    ];

    /*
    |--------------------------------------------------------------------------
    | Type Casting
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'overall_rating'              => 'integer',
        'communication_rating'        => 'float',
        'job_quality_rating'          => 'float',
        'professionalism_rating'      => 'float',
        'average_rating'              => 'float',
        'job_complete_satisfaction'   => 'boolean',
        'created_at'                  => 'datetime',
        'updated_at'                  => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewee()
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }

    public function jobPost()
    {
        return $this->belongsTo(JobPost::class, 'job_post_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods (Optional but Powerful 🔥)
    |--------------------------------------------------------------------------
    */

    public function isContractorToLabor()
    {
        return $this->review_type === 'contractor_to_labor';
    }

    public function isLaborToContractor()
    {
        return $this->review_type === 'labor_to_contractor';
    }
}
