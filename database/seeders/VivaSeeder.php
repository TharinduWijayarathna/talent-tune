<?php

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\User;
use App\Models\Viva;
use Illuminate\Database\Seeder;

class VivaSeeder extends Seeder
{
    public function run(): void
    {
        $inst1 = Institution::where('slug', 'university-tech')->first();
        $inst2 = Institution::where('slug', 'state-university')->first();
        if (! $inst1 && ! $inst2) {
            return;
        }

        $lecturer1 = User::where('email', 'lecturer1@talenttune.com')->first();
        $lecturer2 = User::where('email', 'lecturer2@talenttune.com')->first();
        $lecturer4 = User::where('email', 'lecturer4@talenttune.com')->first();

        $vivas = [];

        if ($inst1 && $lecturer1) {
            $vivas[] = [
                'institution_id' => $inst1->id,
                'lecturer_id' => $lecturer1->id,
                'title' => 'Algorithms and Data Structures - Midterm Viva',
                'description' => 'Oral examination covering sorting, trees, and graph basics.',
                'batch' => 'CS-2024',
                'scheduled_at' => now()->addDays(2),
                'instructions' => 'Prepare your notes. You will have 15 minutes.',
                'status' => 'upcoming',
            ];
            $vivas[] = [
                'institution_id' => $inst1->id,
                'lecturer_id' => $lecturer1->id,
                'title' => 'Database Systems - Final Viva',
                'description' => 'SQL, normalization, and transaction handling.',
                'batch' => 'CS-2023',
                'scheduled_at' => now()->subDays(5),
                'instructions' => 'Bring your project report.',
                'status' => 'completed',
            ];
        }
        if ($inst1 && $lecturer2) {
            $vivas[] = [
                'institution_id' => $inst1->id,
                'lecturer_id' => $lecturer2->id,
                'title' => 'Software Engineering - Sprint Review Viva',
                'description' => 'Discuss your team project and design decisions.',
                'batch' => 'SE-2024',
                'scheduled_at' => now()->addDays(7),
                'instructions' => 'Prepare a 5-minute demo.',
                'status' => 'upcoming',
            ];
        }
        if ($inst2 && $lecturer4) {
            $vivas[] = [
                'institution_id' => $inst2->id,
                'lecturer_id' => $lecturer4->id,
                'title' => 'Data Structures - Lab Viva',
                'description' => 'Practical viva on linked lists and stacks.',
                'batch' => 'SE-2024',
                'scheduled_at' => now()->addDays(1),
                'instructions' => 'Review lab exercises 1–5.',
                'status' => 'active',
            ];
        }

        foreach ($vivas as $data) {
            Viva::firstOrCreate(
                [
                    'institution_id' => $data['institution_id'],
                    'lecturer_id' => $data['lecturer_id'],
                    'title' => $data['title'],
                    'batch' => $data['batch'],
                ],
                $data
            );
        }
    }
}
