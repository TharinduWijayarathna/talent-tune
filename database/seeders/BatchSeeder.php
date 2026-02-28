<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Institution;
use Illuminate\Database\Seeder;

class BatchSeeder extends Seeder
{
    public function run(): void
    {
        $institutions = Institution::all();

        $batchNamesBySlug = [
            'university-tech' => ['CS-2024', 'CS-2023', 'SE-2024'],
            'state-university' => ['SE-2024', 'DS-2024'],
            'tamara-pratt' => ['IT-2024'],
        ];

        foreach ($institutions as $inst) {
            $names = $batchNamesBySlug[$inst->slug] ?? ['Default'];
            foreach ($names as $name) {
                Batch::firstOrCreate(
                    [
                        'institution_id' => $inst->id,
                        'name' => $name,
                    ]
                );
            }
        }
    }
}
