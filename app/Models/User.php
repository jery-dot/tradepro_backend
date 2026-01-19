<?php

namespace App\Models;

use App\Enums\UserType;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'latitude',
        'longitude',
        'city',
        'state',
        'country',
        'job_requirements',
        'rating',
        'status',
        'available_today',
        'profile_image',
    ];

    protected $hidden = [
        'password',
    ];

    public function getLocationTextAttribute(): string
    {
        $location = '';
        if ($this->city) {
            $location .= $this->city;
        }
        if ($this->state) {
            if (! empty($location)) {
                $location .= ', ';
            }
        }
        if ($this->country) {
            if (! empty($location)) {
                $location .= ', ';
            }
            $location .= $this->country;
        }

        return $location;
    }

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'user_type' => UserType::class,
            'job_requirements' => 'array',
        ];
    }

    // JWT methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // Profiles

    public function laborer(): HasOne
    {
        return $this->hasOne(Laborer::class);
    }

    public function subcontractor(): HasOne
    {
        return $this->hasOne(Subcontractor::class);
    }

    public function contractor(): HasOne
    {
        return $this->hasOne(Contractor::class);
    }

    public function apprentice(): HasOne
    {
        return $this->hasOne(Apprentice::class);
    }

    public function receivedReviews()
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    /**
     * Role label from user_type enum
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->user_type) {
            UserType::CONTRACTOR => 'Contractor',
            UserType::SUBCONTRACTOR => 'Subcontractor',
            UserType::LABORER => 'Laborer',
            UserType::APPRENTICE => 'Apprentice',
            default => 'Unknown'
        };
    }

    /**
     * Ratings data (avg + count)
     */
    public function getRatingsDataAttribute()
    {
        $rating = round($this->receivedReviews()->avg('overall_rating') ?? 0, 1);
        $count = $this->receivedReviews()->count();

        return [
            'rating' => $rating,
            'ratings_count' => $count,
        ];
    }

    /**
     * Uploaded document metadata (null-safe)
     */
    public function getUploadedDocumentAttribute()
    {
        if (! $this->profile_document_url) {
            return null;
        }

        return [
            'file_name' => $this->profile_document_name,
            'file_size' => $this->profile_document_size,
            'document_url' => $this->profile_document_url,
        ];
    }

}
