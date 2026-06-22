<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprenticeProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'position_seeking',
        'age',
        'lat',
        'lng',
        'city',
        'location_text',
        'education_experience',
        'trade_school',
        'about_me',
        'resume_file_url',
        'profile_visible',
    ];

    protected $casts = [
        'age'             => 'integer',
        'lat'             => 'float',
        'lng'             => 'float',
        'profile_visible' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
