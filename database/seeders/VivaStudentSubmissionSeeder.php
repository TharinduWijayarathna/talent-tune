<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Viva;
use App\Models\VivaStudentSubmission;
use Illuminate\Database\Seeder;

class VivaStudentSubmissionSeeder extends Seeder
{
    public function run(): void
    {
        $viva = Viva::where('title', 'Database Systems - Final Viva')
            ->where('status', 'completed')
            ->first();
        if (! $viva) {
            return;
        }

        $students = User::where('institution_id', $viva->institution_id)
            ->where('role', 'student')
            ->where('batch', $viva->batch)
            ->limit(3)
            ->get();

        $grades = ['A', 'B+', 'B'];
        foreach ($students as $i => $student) {
            VivaStudentSubmission::firstOrCreate(
                [
                    'viva_id' => $viva->id,
                    'student_id' => $student->id,
                ],
                [
                    'total_score' => 85 - ($i * 5),
                    'grade' => $grades[$i] ?? 'B',
                    'status' => 'completed',
                    'feedback' => 'Good understanding of normalization. Practice more on transaction scenarios.',
                ]
            );
        }
    }
}
