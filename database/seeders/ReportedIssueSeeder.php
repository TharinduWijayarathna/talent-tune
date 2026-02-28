<?php

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\ReportedIssue;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReportedIssueSeeder extends Seeder
{
    public function run(): void
    {
        $inst1 = Institution::where('slug', 'university-tech')->first();
        $inst2 = Institution::where('slug', 'state-university')->first();
        $student1 = User::where('email', 'student1@talenttune.com')->first();
        $student2 = User::where('email', 'student2@talenttune.com')->first();
        $lecturer1 = User::where('email', 'lecturer1@talenttune.com')->first();

        $issues = [];

        if ($inst1 && $student1) {
            $issues[] = [
                'institution_id' => $inst1->id,
                'user_id' => $student1->id,
                'subject' => 'Microphone not working during viva',
                'body' => 'My browser asked for microphone access but the viva page did not detect it. I had to restart the session.',
                'status' => 'reviewed',
                'support_ticket_id' => null,
            ];
            $issues[] = [
                'institution_id' => $inst1->id,
                'user_id' => $student2->id,
                'subject' => 'Score not showing after submission',
                'body' => 'I completed the viva last week but my grade is still not visible. Can someone check?',
                'status' => 'pending',
                'support_ticket_id' => null,
            ];
        }
        if ($inst1 && $lecturer1) {
            $issues[] = [
                'institution_id' => $inst1->id,
                'user_id' => $lecturer1->id,
                'subject' => 'AI questions too easy for advanced batch',
                'body' => 'The generated questions for CS-2023 are not challenging enough. Request option to set difficulty level.',
                'status' => 'escalated',
                'support_ticket_id' => null, // would be set when escalated; we set it below if a ticket exists
            ];
        }
        if ($inst2 && $student1) {
            $student16 = User::where('email', 'student16@talenttune.com')->first();
            if ($student16) {
                $issues[] = [
                    'institution_id' => $inst2->id,
                    'user_id' => $student16->id,
                    'subject' => 'Cannot upload PDF for viva',
                    'body' => 'File upload fails with "file too large" but my PDF is only 2MB.',
                    'status' => 'pending',
                    'support_ticket_id' => null,
                ];
            }
        }

        foreach ($issues as $data) {
            ReportedIssue::firstOrCreate(
                [
                    'institution_id' => $data['institution_id'],
                    'user_id' => $data['user_id'],
                    'subject' => $data['subject'],
                ],
                $data
            );
        }
    }
}
