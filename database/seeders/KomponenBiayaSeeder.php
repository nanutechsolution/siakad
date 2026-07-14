<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KomponenBiayaSeeder extends Seeder
{
    public function run(): void
    {
        $komponen = [
            // 1. Biaya Awal Masuk (Sekali bayar)
            [
                'kode_komponen' => 'REGISTRASI',
                'nama_komponen' => 'Biaya Pendaftaran/Registrasi',
                'tipe_biaya' => 'SEKALI',
                'urutan_prioritas' => 10,
            ],
            [
                'kode_komponen' => 'PKKMB',
                'nama_komponen' => 'Biaya PKKMB',
                'tipe_biaya' => 'SEKALI',
                'urutan_prioritas' => 20,
            ],
            [
                'kode_komponen' => 'ALMAMATER',
                'nama_komponen' => 'Pembayaran Jas Almamater',
                'tipe_biaya' => 'SEKALI',
                'urutan_prioritas' => 30,
            ],
            [
                'kode_komponen' => 'SERAGAM',
                'nama_komponen' => 'Pembayaran Seragam Kuliah',
                'tipe_biaya' => 'SEKALI',
                'urutan_prioritas' => 40,
            ],

            // 2. Biaya Rutin Semester
            [
                'kode_komponen' => 'SPP',
                'nama_komponen' => 'Pembayaran SPP',
                'tipe_biaya' => 'TETAP',
                'urutan_prioritas' => 1,
            ],
            [
                'kode_komponen' => 'UKM',
                'nama_komponen' => 'Biaya Ekstrakurikuler',
                'tipe_biaya' => 'TETAP',
                'urutan_prioritas' => 5,
            ],

            // 3. Biaya Insidental
            [
                'kode_komponen' => 'SERAGAM_LAP',
                'nama_komponen' => 'Pembayaran Seragam Lapangan',
                'tipe_biaya' => 'INSIDENTAL',
                'urutan_prioritas' => 50,
            ],
            [
                'kode_komponen' => 'LAB',
                'nama_komponen' => 'Pembayaran Praktikum Laboratorium',
                'tipe_biaya' => 'INSIDENTAL',
                'urutan_prioritas' => 60,
            ],
            [
                'kode_komponen' => 'PRAKTEK_LAP',
                'nama_komponen' => 'Pembayaran Praktikum Lapangan',
                'tipe_biaya' => 'INSIDENTAL',
                'urutan_prioritas' => 70,
            ],
            [
                'kode_komponen' => 'UKOM',
                'nama_komponen' => 'Pembayaran Uji Kompetensi',
                'tipe_biaya' => 'INSIDENTAL',
                'urutan_prioritas' => 80,
            ],
            [
                'kode_komponen' => 'KKN_PKL',
                'nama_komponen' => 'Biaya KKN/PKL',
                'tipe_biaya' => 'INSIDENTAL',
                'urutan_prioritas' => 90,
            ],
            [
                'kode_komponen' => 'TA',
                'nama_komponen' => 'Biaya Skripsi dan Tugas Akhir',
                'tipe_biaya' => 'INSIDENTAL',
                'urutan_prioritas' => 100,
            ],
            [
                'kode_komponen' => 'YUDISIUM',
                'nama_komponen' => 'Biaya Yudisium',
                'tipe_biaya' => 'INSIDENTAL',
                'urutan_prioritas' => 110,
            ],
            [
                'kode_komponen' => 'WISUDA',
                'nama_komponen' => 'Biaya Wisuda',
                'tipe_biaya' => 'INSIDENTAL',
                'urutan_prioritas' => 120,
            ],
            [
                'kode_komponen' => 'LAIN_LAIN',
                'nama_komponen' => 'Biaya Lain-lain',
                'tipe_biaya' => 'INSIDENTAL',
                'urutan_prioritas' => 999,
            ],
        ];

        foreach ($komponen as $item) {
            DB::table('keuangan_komponen_biaya')->updateOrInsert(
                [
                    'kode_komponen' => $item['kode_komponen'],
                ],
                [
                    'nama_komponen' => $item['nama_komponen'],
                    'tipe_biaya' => $item['tipe_biaya'],
                    'urutan_prioritas' => $item['urutan_prioritas'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}