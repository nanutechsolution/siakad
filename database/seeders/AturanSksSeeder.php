<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RefAturanSks;

class AturanSksSeeder extends Seeder
{
    public function run(): void
    {
        // Data Aturan Beban SKS Standar
        // Format: [Min IPS, Max IPS, Jatah SKS]
        $rules = [
            ['min' => 0.00, 'max' => 1.49, 'sks' => 12],
            ['min' => 1.50, 'max' => 1.99, 'sks' => 15],
            ['min' => 2.00, 'max' => 2.49, 'sks' => 18],
            ['min' => 2.50, 'max' => 2.99, 'sks' => 21],
            ['min' => 3.00, 'max' => 4.00, 'sks' => 24],
        ];

        foreach ($rules as $rule) {
            RefAturanSks::updateOrCreate(
                [
                    'min_ips' => $rule['min'],
                    'max_ips' => $rule['max']
                ],
                [
                    'max_sks' => $rule['sks'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        $this->command->info('Aturan beban SKS berhasil di-generate.');
    }
}
