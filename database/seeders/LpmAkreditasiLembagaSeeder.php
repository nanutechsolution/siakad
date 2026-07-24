<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LpmAkreditasiLembagaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $lembagas = [
            [
                'kode'       => 'BAN-PT',
                'nama'       => 'Badan Akreditasi Nasional Perguruan Tinggi',
                'jenis'      => 'INSTITUSI',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode'       => 'BAN-PT-PRODI',
                'nama'       => 'Badan Akreditasi Nasional Perguruan Tinggi (Program Studi)',
                'jenis'      => 'PRODI',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode'       => 'LAM-PTKes',
                'nama'       => 'Lembaga Akreditasi Mandiri Pendidikan Tinggi Kesehatan',
                'jenis'      => 'PRODI',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode'       => 'LAMEMBA',
                'nama'       => 'Lembaga Akreditasi Mandiri Ekonomi Manajemen Bisnis dan Akuntansi',
                'jenis'      => 'PRODI',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode'       => 'LAMDIK',
                'nama'       => 'Lembaga Akreditasi Mandiri Kependidikan',
                'jenis'      => 'PRODI',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode'       => 'LAM-INFOKOM',
                'nama'       => 'Lembaga Akreditasi Mandiri Informatika dan Komputer',
                'jenis'      => 'PRODI',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode'       => 'LAM-TEKNIK',
                'nama'       => 'Lembaga Akreditasi Mandiri Teknik',
                'jenis'      => 'PRODI',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode'       => 'LAMSAMA',
                'nama'       => 'Lembaga Akreditasi Mandiri Sains Alam dan Ilmu Formal',
                'jenis'      => 'PRODI',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        DB::table('lpm_akreditasi_lembagas')->upsert(
            $lembagas,
            ['kode'],
            ['nama', 'jenis', 'updated_at']
        );
    }
}