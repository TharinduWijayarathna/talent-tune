<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@talenttune.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Institution Users
        $institutions = [
            [
                'name' => 'University of Technology',
                'email' => 'institution1@talenttune.com',
                'password' => Hash::make('password'),
                'role' => 'institution',
                'department' => 'Administration',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'State University',
                'email' => 'institution2@talenttune.com',
                'password' => Hash::make('password'),
                'role' => 'institution',
                'department' => 'Administration',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($institutions as $institution) {
            User::create($institution);
        }

        // Lecturer Users
        $lecturers = [
            [
                'name' => 'Dr. John Smith',
                'email' => 'lecturer1@talenttune.com',
                'password' => Hash::make('password'),
                'role' => 'lecturer',
                'employee_id' => 'EMP001',
                'department' => 'Computer Science',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Dr. Sarah Johnson',
                'email' => 'lecturer2@talenttune.com',
                'password' => Hash::make('password'),
                'role' => 'lecturer',
                'employee_id' => 'EMP002',
                'department' => 'Software Engineering',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Dr. Michael Williams',
                'email' => 'lecturer3@talenttune.com',
                'password' => Hash::make('password'),
                'role' => 'lecturer',
                'employee_id' => 'EMP003',
                'department' => 'Web Development',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Dr. Emily Brown',
                'email' => 'lecturer4@talenttune.com',
                'password' => Hash::make('password'),
                'role' => 'lecturer',
                'employee_id' => 'EMP004',
                'department' => 'Data Structures',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Dr. David Davis',
                'email' => 'lecturer5@talenttune.com',
                'password' => Hash::make('password'),
                'role' => 'lecturer',
                'employee_id' => 'EMP005',
                'department' => 'Operating Systems',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($lecturers as $lecturer) {
            User::create($lecturer);
        }

        // Student Users - Batch CS-2024
        $studentsBatch1 = [
            ['name' => 'Alice Johnson', 'email' => 'student1@talenttune.com', 'student_id' => 'STU001'],
            ['name' => 'Bob Williams', 'email' => 'student2@talenttune.com', 'student_id' => 'STU002'],
            ['name' => 'Charlie Brown', 'email' => 'student3@talenttune.com', 'student_id' => 'STU003'],
            ['name' => 'Diana Martinez', 'email' => 'student4@talenttune.com', 'student_id' => 'STU004'],
            ['name' => 'Ethan Davis', 'email' => 'student5@talenttune.com', 'student_id' => 'STU005'],
            ['name' => 'Fiona Wilson', 'email' => 'student6@talenttune.com', 'student_id' => 'STU006'],
            ['name' => 'George Taylor', 'email' => 'student7@talenttune.com', 'student_id' => 'STU007'],
            ['name' => 'Hannah Anderson', 'email' => 'student8@talenttune.com', 'student_id' => 'STU008'],
            ['name' => 'Ian Thomas', 'email' => 'student9@talenttune.com', 'student_id' => 'STU009'],
            ['name' => 'Julia Jackson', 'email' => 'student10@talenttune.com', 'student_id' => 'STU010'],
        ];

        foreach ($studentsBatch1 as $student) {
            User::create([
                'name' => $student['name'],
                'email' => $student['email'],
                'password' => Hash::make('password'),
                'role' => 'student',
                'student_id' => $student['student_id'],
                'batch' => 'CS-2024',
                'department' => 'Computer Science',
                'email_verified_at' => now(),
            ]);
        }

        // Student Users - Batch CS-2023
        $studentsBatch2 = [
            ['name' => 'Kevin White', 'email' => 'student11@talenttune.com', 'student_id' => 'STU011'],
            ['name' => 'Lily Harris', 'email' => 'student12@talenttune.com', 'student_id' => 'STU012'],
            ['name' => 'Marcus Clark', 'email' => 'student13@talenttune.com', 'student_id' => 'STU013'],
            ['name' => 'Nora Lewis', 'email' => 'student14@talenttune.com', 'student_id' => 'STU014'],
            ['name' => 'Oscar Walker', 'email' => 'student15@talenttune.com', 'student_id' => 'STU015'],
        ];

        foreach ($studentsBatch2 as $student) {
            User::create([
                'name' => $student['name'],
                'email' => $student['email'],
                'password' => Hash::make('password'),
                'role' => 'student',
                'student_id' => $student['student_id'],
                'batch' => 'CS-2023',
                'department' => 'Computer Science',
                'email_verified_at' => now(),
            ]);
        }

        // Student Users - Batch SE-2024
        $studentsBatch3 = [
            ['name' => 'Penelope Hall', 'email' => 'student16@talenttune.com', 'student_id' => 'STU016'],
            ['name' => 'Quinn Allen', 'email' => 'student17@talenttune.com', 'student_id' => 'STU017'],
            ['name' => 'Rachel Young', 'email' => 'student18@talenttune.com', 'student_id' => 'STU018'],
            ['name' => 'Samuel King', 'email' => 'student19@talenttune.com', 'student_id' => 'STU019'],
            ['name' => 'Tina Wright', 'email' => 'student20@talenttune.com', 'student_id' => 'STU020'],
        ];

        foreach ($studentsBatch3 as $student) {
            User::create([
                'name' => $student['name'],
                'email' => $student['email'],
                'password' => Hash::make('password'),
                'role' => 'student',
                'student_id' => $student['student_id'],
                'batch' => 'SE-2024',
                'department' => 'Software Engineering',
                'email_verified_at' => now(),
            ]);
        }

        $this->command->info('Seeded users:');
        $this->command->info('- 1 Admin user');
        $this->command->info('- 2 Institution users');
        $this->command->info('- 5 Lecturer users');
        $this->command->info('- 20 Student users');
    }
}
