<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opportunity extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'apprenticeship_id',
        'user_id',
        'skills_needed',
        'apprenticeship_start_date',
        'duration_weeks',
        'compensation_paid',
        'total_pay_offering',
        'lat',
        'lng',
        'city',
        'title',
        'apprenticeship_description',
    ];

    protected $casts = [
        'skills_needed'           => 'array',
        'apprenticeship_start_date' => 'date',
        'compensation_paid'       => 'boolean',
        'total_pay_offering'      => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
