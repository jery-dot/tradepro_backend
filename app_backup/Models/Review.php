<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'review_code',
        'job_post_id',
        'reviewer_id',
        'reviewee_id',
        'overall_rating',
        'recommendation',
        'communication_rating',
        'job_quality_rating',
        'professionalism_rating',
        'job_complete_satisfaction',
        'comment',
        'average_rating',
    ];

    protected function casts(): array
    {
        return [
            'communication_rating'      => 'float',
            'job_quality_rating'        => 'float',
            'professionalism_rating'    => 'float',
            'average_rating'            => 'float',
            'job_complete_satisfaction' => 'boolean',
        ];
    }

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

}
