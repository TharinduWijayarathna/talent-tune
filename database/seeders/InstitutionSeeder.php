<?php

namespace Database\Seeders;

use App\Models\Institution;
use App\Services\Application\InstitutionService;
use Illuminate\Database\Seeder;

class InstitutionSeeder extends Seeder
{
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
                'is_active' => true,
                'subscription_status' => 'active',
                'trial_ends_at' => null,
            ],
            [
                'name' => 'State University',
                'slug' => 'state-university',
                'email' => 'info@state-university.edu',
                'contact_person' => 'Dr. Sarah Johnson',
                'phone' => '+1 (555) 987-6543',
                'address' => '456 State Avenue, Capital City, CC 67890',
                'is_active' => true,
                'subscription_status' => null,
                'trial_ends_at' => now()->addDays(14),
            ],
            [
                'name' => 'Tamara Pratt College',
                'slug' => 'tamara-pratt',
                'email' => 'contact@tamara-pratt.edu',
                'contact_person' => 'Dr. Tamara Pratt',
                'phone' => '+1 (555) 555-5555',
                'address' => '789 Education Boulevard, Academic City, AC 54321',
                'is_active' => false,
                'subscription_status' => null,
                'trial_ends_at' => null,
            ],
        ];

        $seededSlugs = [];
        foreach ($institutions as $data) {
            Institution::updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, [
                    'settings' => [
                        'email' => $data['email'],
                        'contact_person' => $data['contact_person'],
                        'phone' => $data['phone'],
                        'address' => $data['address'],
                    ],
                ])
            );
            $seededSlugs[] = $data['slug'];
        }

        // In production, create subdomains via Dockploy API for each seeded institution
        if (app()->environment('production')) {
            $institutionService = app(InstitutionService::class);
            foreach ($seededSlugs as $slug) {
                $institution = Institution::where('slug', $slug)->first();
                if ($institution) {
                    $institutionService->createSubdomainForInstitution($institution);
                }
            }
        }
    }
}
