<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LpmStandarSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $standars = [
            [
                'kode_standar' => 'STD-AKD-001',
                'nama_standar' => 'Standar Kompetensi Lulusan',
                'kategori' => 'AKADEMIK',
                'kategori_standar_id' => null,
                'pernyataan_standar' => 'Lulusan memiliki kompetensi sesuai capaian pembelajaran lulusan (CPL).',
                'target_pencapaian' => 100,
                'satuan' => '%',
                'versi' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_standar' => 'STD-AKD-002',
                'nama_standar' => 'Standar Isi Pembelajaran',
                'kategori' => 'AKADEMIK',
                'kategori_standar_id' => null,
                'pernyataan_standar' => 'Kurikulum disusun sesuai SN-Dikti dan kebutuhan pengguna lulusan.',
                'target_pencapaian' => 100,
                'satuan' => '%',
                'versi' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_standar' => 'STD-AKD-003',
                'nama_standar' => 'Standar Proses Pembelajaran',
                'kategori' => 'AKADEMIK',
                'kategori_standar_id' => null,
                'pernyataan_standar' => 'Proses pembelajaran dilaksanakan sesuai RPS dan standar mutu.',
                'target_pencapaian' => 100,
                'satuan' => '%',
                'versi' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_standar' => 'STD-AKD-004',
                'nama_standar' => 'Standar Penilaian Pembelajaran',
                'kategori' => 'AKADEMIK',
                'kategori_standar_id' => null,
                'pernyataan_standar' => 'Penilaian dilakukan secara objektif, transparan, dan akuntabel.',
                'target_pencapaian' => 100,
                'satuan' => '%',
                'versi' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_standar' => 'STD-NAKD-001',
                'nama_standar' => 'Standar Tata Pamong',
                'kategori' => 'NON-AKADEMIK',
                'kategori_standar_id' => null,
                'pernyataan_standar' => 'Tata pamong dilaksanakan berdasarkan prinsip good university governance.',
                'target_pencapaian' => 100,
                'satuan' => '%',
                'versi' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_standar' => 'STD-NAKD-002',
                'nama_standar' => 'Standar Sumber Daya Manusia',
                'kategori' => 'NON-AKADEMIK',
                'kategori_standar_id' => null,
                'pernyataan_standar' => 'Pengelolaan SDM dilakukan secara profesional dan berkelanjutan.',
                'target_pencapaian' => 100,
                'satuan' => '%',
                'versi' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_standar' => 'STD-NAKD-003',
                'nama_standar' => 'Standar Keuangan',
                'kategori' => 'NON-AKADEMIK',
                'kategori_standar_id' => null,
                'pernyataan_standar' => 'Pengelolaan keuangan dilakukan secara efektif, efisien, dan akuntabel.',
                'target_pencapaian' => 100,
                'satuan' => '%',
                'versi' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_standar' => 'STD-NAKD-004',
                'nama_standar' => 'Standar Sarana dan Prasarana',
                'kategori' => 'NON-AKADEMIK',
                'kategori_standar_id' => null,
                'pernyataan_standar' => 'Sarana dan prasarana memenuhi kebutuhan pembelajaran dan layanan.',
                'target_pencapaian' => 100,
                'satuan' => '%',
                'versi' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('lpm_standars')->upsert(
            $standars,
            ['kode_standar', 'versi'],
            [
                'nama_standar',
                'kategori',
                'kategori_standar_id',
                'pernyataan_standar',
                'target_pencapaian',
                'satuan',
                'is_active',
                'updated_at',
            ]
        );
    }
}