<?php

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $inst1 = Institution::where('slug', 'university-tech')->first();
        $inst2 = Institution::where('slug', 'state-university')->first();

        $payments = [];

        if ($inst1) {
            $payments[] = [
                'institution_id' => $inst1->id,
                'currency' => 'USD',
                'amount' => 9900, // $99.00 in cents
                'status' => 'completed',
                'gateway' => 'stripe',
                'external_id' => 'seeder_pi_'.uniqid(),
                'paid_at' => now()->subDays(15),
            ];
            $payments[] = [
                'institution_id' => $inst1->id,
                'currency' => 'USD',
                'amount' => 9900,
                'status' => 'completed',
                'gateway' => 'stripe',
                'external_id' => 'seeder_pi_'.uniqid(),
                'paid_at' => now()->subDays(45),
            ];
        }
        if ($inst2) {
            $payments[] = [
                'institution_id' => $inst2->id,
                'currency' => 'USD',
                'amount' => 9900,
                'status' => 'pending',
                'gateway' => null,
                'external_id' => null,
                'paid_at' => null,
            ];
        }

        foreach ($payments as $data) {
            $key = [
                'institution_id' => $data['institution_id'],
                'amount' => $data['amount'],
                'paid_at' => $data['paid_at'],
            ];
            Payment::firstOrCreate($key, $data);
        }
    }
}
