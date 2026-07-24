<?php

declare(strict_types=1);

namespace Database\Seeders\Lpm\Master;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LpmIndikatorSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $standars = DB::table('lpm_standars')
            ->pluck('id', 'kode_standar');

        $master = [

            'SNDIKTI-01' => [

                [
                    'kode' => 'IKU-01',
                    'nama' => 'Persentase Lulusan Tepat Waktu',
                    'satuan' => '%',
                    'bobot' => 15,
                    'is_iku' => true,
                    'deskripsi' => 'Persentase mahasiswa yang lulus sesuai masa studi.',
                ],

                [
                    'kode' => 'IKU-02',
                    'nama' => 'Rata-rata IPK Lulusan',
                    'satuan' => 'IPK',
                    'bobot' => 10,
                    'is_iku' => true,
                    'deskripsi' => 'Nilai rata-rata IPK lulusan.',
                ],

                [
                    'kode' => 'IKU-03',
                    'nama' => 'Persentase Lulusan Bekerja < 6 Bulan',
                    'satuan' => '%',
                    'bobot' => 15,
                    'is_iku' => true,
                    'deskripsi' => 'Tracer Study.',
                ],

            ],

            'SNDIKTI-02' => [

                [
                    'kode' => 'STD2-01',
                    'nama' => 'Kurikulum Berbasis OBE',
                    'satuan' => '%',
                    'bobot' => 10,
                    'is_iku' => false,
                    'deskripsi' => 'Implementasi OBE.',
                ],

                [
                    'kode' => 'STD2-02',
                    'nama' => 'Review Kurikulum Tepat Waktu',
                    'satuan' => '%',
                    'bobot' => 10,
                    'is_iku' => false,
                    'deskripsi' => 'Review minimal 4 tahun.',
                ],

            ],

            'SNDIKTI-03' => [

                [
                    'kode' => 'STD3-01',
                    'nama' => 'Perkuliahan Sesuai RPS',
                    'satuan' => '%',
                    'bobot' => 15,
                    'is_iku' => false,
                    'deskripsi' => 'Kesesuaian pembelajaran terhadap RPS.',
                ],

                [
                    'kode' => 'STD3-02',
                    'nama' => 'Kehadiran Dosen',
                    'satuan' => '%',
                    'bobot' => 10,
                    'is_iku' => false,
                    'deskripsi' => 'Minimal 14 pertemuan.',
                ],

                [
                    'kode' => 'STD3-03',
                    'nama' => 'Kehadiran Mahasiswa',
                    'satuan' => '%',
                    'bobot' => 10,
                    'is_iku' => false,
                    'deskripsi' => 'Persentase kehadiran mahasiswa.',
                ],

            ],

            'SNDIKTI-04' => [

                [
                    'kode' => 'STD4-01',
                    'nama' => 'Nilai Dipublikasikan Tepat Waktu',
                    'satuan' => '%',
                    'bobot' => 10,
                    'is_iku' => false,
                    'deskripsi' => 'Publikasi nilai sesuai kalender akademik.',
                ],

            ],

        ];

        $rows = [];

        foreach ($master as $kodeStandar => $indikators) {

            if (! isset($standars[$kodeStandar])) {
                continue;
            }

            foreach ($indikators as $item) {

                $rows[] = [

                    'standar_id' => $standars[$kodeStandar],

                    'kode_indikator' => $item['kode'],

                    'nama_indikator' => $item['nama'],

                    'slug' => Str::slug($item['kode'] . '-' . $item['nama']),

                    'satuan' => $item['satuan'],

                    'deskripsi' => $item['deskripsi'],

                    'bobot' => $item['bobot'],

                    'is_iku' => $item['is_iku'],

                    'is_active' => true,

                    'sumber_data_siakad' => null,

                    'calculation_method' => null,

                    'calculation_params' => null,

                    'created_at' => $now,

                    'updated_at' => $now,

                ];
            }
        }

        DB::table('lpm_indikators')->upsert(

            $rows,

            ['slug'],

            [

                'standar_id',

                'kode_indikator',

                'nama_indikator',

                'satuan',

                'deskripsi',

                'bobot',

                'is_iku',

                'is_active',

                'sumber_data_siakad',

                'calculation_method',

                'calculation_params',

                'updated_at',

            ]

        );
    }
}
