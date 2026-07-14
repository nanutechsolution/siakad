<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RefSkalaNilai;

class SkalaNilaiSeeder extends Seeder
{
    /**
     * Jalankan database seeder untuk standar skala nilai.
     */
    public function run(): void
    {
        $data = [
            [
                'huruf' => 'A',
                'bobot_indeks' => 4.00,
                'nilai_min' => 86.00,
                'nilai_max' => 100.00,
                'is_lulus' => true,
            ],
            [
                'huruf' => 'B',
                'bobot_indeks' => 3.00,
                'nilai_min' => 76.00,
                'nilai_max' => 85.99,
                'is_lulus' => true,
            ],
            [
                'huruf' => 'C',
                'bobot_indeks' => 2.00,
                'nilai_min' => 66.00,
                'nilai_max' => 75.99,
                'is_lulus' => true,
            ],
            [
                'huruf' => 'D',
                'bobot_indeks' => 1.00,
                'nilai_min' => 56.00,
                'nilai_max' => 65.99,
                'is_lulus' => true,
            ],
            [
                'huruf' => 'E',
                'bobot_indeks' => 0.00,
                'nilai_min' => 0.00,
                'nilai_max' => 55.99,
                'is_lulus' => false,
            ],
        ];

        foreach ($data as $item) {
            RefSkalaNilai::updateOrCreate(
                ['huruf' => $item['huruf']], // Kunci pencarian
                array_merge($item, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('Skala nilai berhasil di-generate.');
    }
}
