<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


use Illuminate\Support\Facades\DB;

class ConditionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('conditions')->truncate(); // optional in local/dev

        DB::table('conditions')->insert([
            [
                'id'         => 1,
                'name'       => 'New',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => 2,
                'name'       => 'Used',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => 3,
                'name'       => 'Refurbished',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => 4,
                'name'       => 'For Parts / Not Working',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}