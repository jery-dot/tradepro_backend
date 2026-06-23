<?php

namespace Database\Seeders;

use App\Models\JobRequirement;
use Illuminate\Database\Seeder;

class JobRequirementSeeder extends Seeder
{
    /**
     * Seed the job_requirements table with default values.
     */
    public function run(): void
    {
        // Optional: clear existing data if you want fixed IDs
        // JobRequirement::truncate();

        $data = [
            [
                'id'   => 1,
                'name' => 'Background check requirement',
                'slug' => 'background_check_requirement',
            ],
            [
                'id'   => 2,
                'name' => 'Insurance required',
                'slug' => 'insurance_required',
            ],
            [
                'id'   => 3,
                'name' => 'Drug screening',
                'slug' => 'drug_screening',
            ],
            [
                'id'   => 6,
                'name' => 'Safety certification',
                'slug' => 'safety_certification',
            ],
        ];

        JobRequirement::upsert($data, ['id'], ['name', 'slug']);
    }
}
