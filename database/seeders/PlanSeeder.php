<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ✅ Disable foreign key checks for safe truncate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('plans')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Start inserting the plans to the DB
        Plan::create([
            'name' => 'Laborer',
            'stripe_price_id' => env('STRIPE_LABORER_PRICE_ID', 'prod_TzoNRUTmQU26Ap'),
            'price' => 19.00,
            'features' => [
                'Unlimited job applications',
                'Direct messaging with employers',
                'Marketplace access',
                'Profit visibility',
                'Job alerts',
                'Rating system'
            ],
        ]);

        Plan::create([
            'name' => 'Contractor/Subcontractor',
            'stripe_price_id' => env('STRIPE_CONTRACTOR_PRICE_ID', 'prod_TzoOqZzxGqoxxu'),
            'price' => 59.00,
            'features' => [
                'Unlimited job applications',
                'Access to laborer database',
                'Marketplace access',
                'Direct messaging',
                'Applicant management',
                'Featured posting options',
                'Access to apprentice hub'
            ],
        ]);

        Plan::create([
            'name' => 'Apprentice',
            'stripe_price_id' => env('STRIPE_APPRENTICE_PRICE_ID', 'prod_TzoOA2utngik0n'),
            'price' => 9.99,
            'trial_days' => 90, // Free 3 months
            'features' => [
                'Access to apprenticeship hub',
                'Connect with mentors',
                'Marketplace access',
                'Educational resources',
                'Profile visibility'
            ],
        ]);


    }
}
