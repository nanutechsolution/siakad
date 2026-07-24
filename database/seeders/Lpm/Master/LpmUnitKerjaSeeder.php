<?php

declare(strict_types=1);

namespace Database\Seeders\Lpm\Master;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LpmUnitKerjaSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rows = [

            [
                'kode_unit' => 'REKTORAT',
                'nama_unit' => 'Rektorat',
                'jenis_unit' => 'UNIVERSITAS',
            ],

            [
                'kode_unit' => 'LPM',
                'nama_unit' => 'Lembaga Penjaminan Mutu',
                'jenis_unit' => 'LEMBAGA',
            ],

            [
                'kode_unit' => 'LPPM',
                'nama_unit' => 'Lembaga Penelitian dan Pengabdian',
                'jenis_unit' => 'LEMBAGA',
            ],

            [
                'kode_unit' => 'BAAK',
                'nama_unit' => 'Biro Administrasi Akademik',
                'jenis_unit' => 'BIRO',
            ],

            [
                'kode_unit' => 'BAUK',
                'nama_unit' => 'Biro Administrasi Umum dan Keuangan',
                'jenis_unit' => 'BIRO',
            ],

            [
                'kode_unit' => 'UPT-TIK',
                'nama_unit' => 'UPT Teknologi Informasi',
                'jenis_unit' => 'UPT',
            ],

            [
                'kode_unit' => 'PERPUSTAKAAN',
                'nama_unit' => 'UPT Perpustakaan',
                'jenis_unit' => 'UPT',
            ],

            [
                'kode_unit' => 'FKIP',
                'nama_unit' => 'Fakultas Keguruan dan Ilmu Pendidikan',
                'jenis_unit' => 'FAKULTAS',
            ],

            [
                'kode_unit' => 'FEB',
                'nama_unit' => 'Fakultas Ekonomi dan Bisnis',
                'jenis_unit' => 'FAKULTAS',
            ],

            [
                'kode_unit' => 'HUKUM',
                'nama_unit' => 'Fakultas Hukum',
                'jenis_unit' => 'FAKULTAS',
            ],

        ];

        foreach ($rows as &$row) {

            $row['parent_id'] = null;
            $row['is_active'] = true;
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        DB::table('lpm_unit_kerjas')->upsert(
            $rows,
            ['kode_unit'],
            [
                'nama_unit',
                'jenis_unit',
                'parent_id',
                'is_active',
                'updated_at',
            ]
        );
    }
}
