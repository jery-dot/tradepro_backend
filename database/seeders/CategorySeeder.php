<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->truncate(); // optional in local/dev

        DB::table('categories')->insert([
            [
                'id'         => 1,
                'name'       => 'Existing Companies',
                'image'      => 'https://api.tradepro.services/listings/categories/existing_companies.jpg',
                'is_popular' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => 2,
                'name'       => 'Materials',
                'image'      => 'https://api.tradepro.services/listings/categories/materials.jpg',
                'is_popular' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => 3,
                'name'       => 'Tools & Equipments',
                'image'      => 'https://api.tradepro.services/listings/categories/tools_equipments.jpg',
                'is_popular' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => 6,
                'name'       => 'Vehicles',
                'image'      => 'https://api.tradepro.services/listings/categories/vehicles.jpg',
                'is_popular' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]); // Using DB::table()->insert() is common for static lookup data.[web:43][web:46]
    }
}
