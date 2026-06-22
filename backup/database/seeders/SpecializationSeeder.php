<?php

namespace Database\Seeders;

use App\Models\Specialization;
use Illuminate\Database\Seeder;

class SpecializationSeeder extends Seeder
{
    public function run(): void
    {
        // Optional: clear existing rows before seeding
        // Specialization::truncate();

        $data = [
            ['id' => 1, 'name' => 'Carpentry'],
            ['id' => 2, 'name' => 'Electrical'],
            ['id' => 3, 'name' => 'Plumbing'],
            ['id' => 6, 'name' => 'Others'],
        ];

        // Insert multiple rows at once
        Specialization::insert($data);
    }
}
