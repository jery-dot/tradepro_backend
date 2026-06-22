<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; // 🔥 Crucial pour l'authentification
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'admins';

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Les attributs qui doivent être masqués pour la sérialisation (tableaux, API).
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed', // 🔥 Hache le mot de passe automatiquement à l'insertion/mise à jour
        ];
    }
}