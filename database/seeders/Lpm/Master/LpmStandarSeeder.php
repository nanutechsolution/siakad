<?php

declare(strict_types=1);

namespace Database\Seeders\Lpm\Master;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LpmStandarSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = DB::table('lpm_kategori_standars')
            ->pluck('id', 'nama');

        $standars = [

            [
                'kode_standar' => 'SNDIKTI-01',
                'nama_standar' => 'Standar Kompetensi Lulusan',
                'kategori' => 'AKADEMIK',
                'kategori_standar_id' => $kategori['Standar Nasional Pendidikan'],
            ],

            [
                'kode_standar' => 'SNDIKTI-02',
                'nama_standar' => 'Standar Isi Pembelajaran',
                'kategori' => 'AKADEMIK',
                'kategori_standar_id' => $kategori['Standar Nasional Pendidikan'],
            ],

            [
                'kode_standar' => 'SNDIKTI-03',
                'nama_standar' => 'Standar Proses Pembelajaran',
                'kategori' => 'AKADEMIK',
                'kategori_standar_id' => $kategori['Standar Nasional Pendidikan'],
            ],

            [
                'kode_standar' => 'SNDIKTI-04',
                'nama_standar' => 'Standar Penilaian Pembelajaran',
                'kategori' => 'AKADEMIK',
                'kategori_standar_id' => $kategori['Standar Nasional Pendidikan'],
            ],

            [
                'kode_standar' => 'SNDIKTI-05',
                'nama_standar' => 'Standar Dosen dan Tenaga Kependidikan',
                'kategori' => 'AKADEMIK',
                'kategori_standar_id' => $kategori['Standar Nasional Pendidikan'],
            ],

            [
                'kode_standar' => 'SNDIKTI-06',
                'nama_standar' => 'Standar Sarana dan Prasarana',
                'kategori' => 'NON-AKADEMIK',
                'kategori_standar_id' => $kategori['Standar Nasional Pendidikan'],
            ],

            [
                'kode_standar' => 'SNDIKTI-07',
                'nama_standar' => 'Standar Pengelolaan',
                'kategori' => 'NON-AKADEMIK',
                'kategori_standar_id' => $kategori['Standar Nasional Pendidikan'],
            ],

            [
                'kode_standar' => 'SNDIKTI-08',
                'nama_standar' => 'Standar Pembiayaan',
                'kategori' => 'NON-AKADEMIK',
                'kategori_standar_id' => $kategori['Standar Nasional Pendidikan'],
            ],

        ];

        foreach ($standars as &$item) {

            $item['pernyataan_standar'] =
                'Institusi wajib memenuhi standar ini sesuai kebijakan mutu SPMI.';

            $item['target_pencapaian'] = 100;
            $item['satuan'] = '%';
            $item['versi'] = 1;
            $item['is_active'] = true;
            $item['created_at'] = now();
            $item['updated_at'] = now();
        }

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
