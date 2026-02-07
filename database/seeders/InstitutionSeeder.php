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
            [
                'name' => 'Tamara Pratt University',
                'slug' => 'tamara-pratt',
                'email' => 'contact@tamara-pratt.edu',
                'contact_person' => 'Dr. Tamara Pratt',
                'phone' => '+1 (555) 555-5555',
                'address' => '789 Education Boulevard, Academic City, AC 54321',
                'primary_color' => '#8b5cf6',
                'is_active' => true, // Pre-activated for testing
            ],
        ];

        foreach ($institutions as $institutionData) {
            Institution::updateOrCreate(
                ['slug' => $institutionData['slug']],
                $institutionData
            );
        }

        $this->command->info('Seeded '.count($institutions).' institutions');
    }
}
