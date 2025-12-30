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
        'available_today'
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'user_type' => UserType::class
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
}
