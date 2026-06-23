<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'admin@tradepro.com'], // Évite les doublons si vous lancez le seeder plusieurs fois
            [
                'name'            => 'Super Admin',
                'password'        => Hash::make('password123'), // Changez ce mot de passe en production !
                // 'user_type'       => 0, // Optionnel : mettez le type par défaut requis pour votre logique
                // 'available_today' => false,
                // 'is_admin'        => true, // C'est cette ligne qui lui donne l'accès admin !
            ]
        );

        $this->command->info('Admin user successfully created!');
    }
}