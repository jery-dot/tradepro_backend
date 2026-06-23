<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetOtp extends Model
{
    protected $fillable = [
        'email',
        'otp',
        'expires_at',
        'reset_token',
        'reset_token_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at'             => 'datetime',
            'reset_token_expires_at' => 'datetime',
        ];
    }
}
