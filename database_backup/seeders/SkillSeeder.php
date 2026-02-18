<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SkillSeeder extends Seeder
{
    /**
     * Seed the skills table with default skills.
     */
    public function run(): void
    {
        $skills = [
            [
                'code' => 'skill_001',
                'name' => 'Content Writing',
                'slug' => 'content-writing',
            ],
            [
                'code' => 'skill_002',
                'name' => 'Copywriting',
                'slug' => 'copywriting',
            ],
            [
                'code' => 'skill_003',
                'name' => 'Creative Writing',
                'slug' => 'creative-writing',
            ],
            [
                'code' => 'skill_004',
                'name' => 'SEO Writing',
                'slug' => 'seo-writing',
            ],
            // add more skills here...
        ];

        // If you prefer to generate slug dynamically:
        // foreach ($skills as &$skill) {
        //     $skill['slug'] = Str::slug($skill['name']);
        // }

        Skill::upsert(
            $skills,
            ['code'],          // unique key
            ['name', 'slug']   // columns to update if code exists
        );
    }
}
