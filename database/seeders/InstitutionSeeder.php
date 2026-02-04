<?php

namespace Database\Seeders;

use App\Models\Institution;
use Illuminate\Database\Seeder;

class InstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $institutions = [
            [
                'name' => 'University of Technology',
                'slug' => 'university-tech',
                'email' => 'contact@university-tech.edu',
                'contact_person' => 'Dr. John Smith',
                'phone' => '+1 (555) 123-4567',
                'address' => '123 University Street, Tech City, TC 12345',
                'primary_color' => '#3b82f6',
                'is_active' => true, // Pre-activated for testing
            ],
            [
                'name' => 'State University',
                'slug' => 'state-university',
                'email' => 'info@state-university.edu',
                'contact_person' => 'Dr. Sarah Johnson',
                'phone' => '+1 (555) 987-6543',
                'address' => '456 State Avenue, Capital City, CC 67890',
                'primary_color' => '#10b981',
                'is_active' => true, // Pre-activated for testing
            ],
        ];

        foreach ($institutions as $institution) {
            Institution::create($institution);
        }

        $this->command->info('Seeded 2 institutions');
    }
}
