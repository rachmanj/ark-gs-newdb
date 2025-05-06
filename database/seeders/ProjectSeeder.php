<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $projects = [
            [
                'code' => '017C',
                'name' => 'Project 017C',
                'is_active' => true
            ],
            [
                'code' => '021C',
                'name' => 'Project 021C',
                'is_active' => true
            ],
            // Add more projects as needed
        ];

        foreach ($projects as $projectData) {
            Project::firstOrCreate(
                ['code' => $projectData['code']],
                $projectData
            );
        }
    }
} 