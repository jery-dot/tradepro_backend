<?php

namespace Database\Seeders;

use App\Models\TradeInterest;
use Illuminate\Database\Seeder;

class TradeInterestSeeder extends Seeder
{
    /**
     * Seed the trade_interests table with default values.
     */
    public function run(): void
    {
        // Optional: clear existing data if you want fixed IDs
        // TradeInterest::truncate();

        $data = [
            ['id' => 1, 'name' => 'Carpentry'],
            ['id' => 2, 'name' => 'Electrical'],
            ['id' => 3, 'name' => 'Plumbing'],
            ['id' => 4, 'name' => 'HVAC'],
            ['id' => 5, 'name' => 'Masonry'],
            ['id' => 6, 'name' => 'Roofing'],
        ];

        // Insert or update to allow reseeding safely
        TradeInterest::upsert($data, ['id'], ['name']);
    }
}
