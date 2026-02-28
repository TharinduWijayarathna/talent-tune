<?php

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use Illuminate\Database\Seeder;

class SupportTicketSeeder extends Seeder
{
    public function run(): void
    {
        $inst1 = Institution::where('slug', 'university-tech')->first();
        $inst2 = Institution::where('slug', 'state-university')->first();
        $adminUser = User::where('email', 'admin@talenttune.com')->first();
        $instUser1 = User::where('email', 'institution1@talenttune.com')->first();
        $instUser2 = User::where('email', 'institution2@talenttune.com')->first();

        if (! $adminUser) {
            return;
        }

        $tickets = [];

        if ($inst1 && $instUser1) {
            $tickets[] = [
                'institution_id' => $inst1->id,
                'user_id' => $instUser1->id,
                'subject' => 'Billing question about monthly plan',
                'body' => 'We would like to upgrade our plan next month. Can you confirm the process and pricing?',
                'status' => 'answered',
                'replies' => [
                    ['user_id' => $adminUser->id, 'body' => 'Thank you for reaching out. You can upgrade from the Payment page in your dashboard. The new rate will apply from the next billing cycle.'],
                ],
            ];
            $tickets[] = [
                'institution_id' => $inst1->id,
                'user_id' => $instUser1->id,
                'subject' => 'Unable to add new batch',
                'body' => 'When I try to create a new batch called "CS-2025", I get an error. Please advise.',
                'status' => 'open',
                'replies' => [],
            ];
        }
        if ($inst2 && $instUser2) {
            $tickets[] = [
                'institution_id' => $inst2->id,
                'user_id' => $instUser2->id,
                'subject' => 'Trial period extension',
                'body' => 'Our trial ends next week. We are still evaluating. Is an extension possible?',
                'status' => 'closed',
                'replies' => [
                    ['user_id' => $adminUser->id, 'body' => 'We can extend your trial by 7 days. I have applied the extension. Your new trial end date is in your institution settings.'],
                ],
            ];
        }

        foreach ($tickets as $data) {
            $replies = $data['replies'] ?? [];
            unset($data['replies']);

            $ticket = SupportTicket::firstOrCreate(
                [
                    'institution_id' => $data['institution_id'],
                    'user_id' => $data['user_id'],
                    'subject' => $data['subject'],
                ],
                $data
            );

            foreach ($replies as $reply) {
                SupportTicketReply::firstOrCreate(
                    [
                        'support_ticket_id' => $ticket->id,
                        'user_id' => $reply['user_id'],
                        'body' => $reply['body'],
                    ]
                );
            }
        }
    }
}
