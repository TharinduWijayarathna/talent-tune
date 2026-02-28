<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            InstitutionSeeder::class,
            UserSeeder::class,
            BatchSeeder::class,
            VivaSeeder::class,
            VivaStudentSubmissionSeeder::class,
            SupportTicketSeeder::class,
            ReportedIssueSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}
