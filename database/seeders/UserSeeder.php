<?php

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $inst1 = Institution::where('slug', 'university-tech')->first();
        $inst2 = Institution::where('slug', 'state-university')->first();

        // Admin (no institution)
        User::firstOrCreate(
            ['email' => 'admin@talenttune.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'institution_id' => null,
                'email_verified_at' => now(),
            ]
        );

        // Institution admins
        if ($inst1) {
            User::firstOrCreate(
                ['email' => 'institution1@talenttune.com'],
                [
                    'name' => 'University Admin',
                    'password' => Hash::make('password'),
                    'role' => 'institution',
                    'institution_id' => $inst1->id,
                    'department' => 'Administration',
                    'email_verified_at' => now(),
                ]
            );
        }
        if ($inst2) {
            User::firstOrCreate(
                ['email' => 'institution2@talenttune.com'],
                [
                    'name' => 'State University Admin',
                    'password' => Hash::make('password'),
                    'role' => 'institution',
                    'institution_id' => $inst2->id,
                    'department' => 'Administration',
                    'email_verified_at' => now(),
                ]
            );
        }

        // Lecturers
        $lecturers = [
            ['name' => 'Dr. John Smith', 'email' => 'lecturer1@talenttune.com', 'employee_id' => 'EMP001', 'department' => 'Computer Science', 'inst' => $inst1],
            ['name' => 'Dr. Sarah Johnson', 'email' => 'lecturer2@talenttune.com', 'employee_id' => 'EMP002', 'department' => 'Software Engineering', 'inst' => $inst1],
            ['name' => 'Dr. Michael Williams', 'email' => 'lecturer3@talenttune.com', 'employee_id' => 'EMP003', 'department' => 'Web Development', 'inst' => $inst1],
            ['name' => 'Dr. Emily Brown', 'email' => 'lecturer4@talenttune.com', 'employee_id' => 'EMP004', 'department' => 'Data Structures', 'inst' => $inst2],
            ['name' => 'Dr. David Davis', 'email' => 'lecturer5@talenttune.com', 'employee_id' => 'EMP005', 'department' => 'Operating Systems', 'inst' => $inst2],
        ];
        foreach ($lecturers as $l) {
            if ($l['inst']) {
                User::firstOrCreate(
                    ['email' => $l['email']],
                    [
                        'name' => $l['name'],
                        'password' => Hash::make('password'),
                        'role' => 'lecturer',
                        'institution_id' => $l['inst']->id,
                        'employee_id' => $l['employee_id'],
                        'department' => $l['department'],
                        'email_verified_at' => now(),
                    ]
                );
            }
        }

        // Students – batch CS-2024 (inst1)
        $studentsBatch1 = [
            ['name' => 'Alice Johnson', 'email' => 'student1@talenttune.com', 'student_id' => 'STU001'],
            ['name' => 'Bob Williams', 'email' => 'student2@talenttune.com', 'student_id' => 'STU002'],
            ['name' => 'Charlie Brown', 'email' => 'student3@talenttune.com', 'student_id' => 'STU003'],
            ['name' => 'Diana Martinez', 'email' => 'student4@talenttune.com', 'student_id' => 'STU004'],
            ['name' => 'Ethan Davis', 'email' => 'student5@talenttune.com', 'student_id' => 'STU005'],
            ['name' => 'Fiona Wilson', 'email' => 'student6@talenttune.com', 'student_id' => 'STU006'],
        ];
        foreach ($studentsBatch1 as $s) {
            if ($inst1) {
                User::firstOrCreate(
                    ['email' => $s['email']],
                    [
                        'name' => $s['name'],
                        'password' => Hash::make('password'),
                        'role' => 'student',
                        'institution_id' => $inst1->id,
                        'student_id' => $s['student_id'],
                        'batch' => 'CS-2024',
                        'department' => 'Computer Science',
                        'email_verified_at' => now(),
                    ]
                );
            }
        }

        // Students – batch CS-2023 (inst1)
        $studentsBatch2 = [
            ['name' => 'Kevin White', 'email' => 'student11@talenttune.com', 'student_id' => 'STU011'],
            ['name' => 'Lily Harris', 'email' => 'student12@talenttune.com', 'student_id' => 'STU012'],
            ['name' => 'Marcus Clark', 'email' => 'student13@talenttune.com', 'student_id' => 'STU013'],
        ];
        foreach ($studentsBatch2 as $s) {
            if ($inst1) {
                User::firstOrCreate(
                    ['email' => $s['email']],
                    [
                        'name' => $s['name'],
                        'password' => Hash::make('password'),
                        'role' => 'student',
                        'institution_id' => $inst1->id,
                        'student_id' => $s['student_id'],
                        'batch' => 'CS-2023',
                        'department' => 'Computer Science',
                        'email_verified_at' => now(),
                    ]
                );
            }
        }

        // Students – batch SE-2024 (inst2)
        $studentsBatch3 = [
            ['name' => 'Penelope Hall', 'email' => 'student16@talenttune.com', 'student_id' => 'STU016'],
            ['name' => 'Quinn Allen', 'email' => 'student17@talenttune.com', 'student_id' => 'STU017'],
            ['name' => 'Rachel Young', 'email' => 'student18@talenttune.com', 'student_id' => 'STU018'],
        ];
        foreach ($studentsBatch3 as $s) {
            if ($inst2) {
                User::firstOrCreate(
                    ['email' => $s['email']],
                    [
                        'name' => $s['name'],
                        'password' => Hash::make('password'),
                        'role' => 'student',
                        'institution_id' => $inst2->id,
                        'student_id' => $s['student_id'],
                        'batch' => 'SE-2024',
                        'department' => 'Software Engineering',
                        'email_verified_at' => now(),
                    ]
                );
            }
        }
    }
}
