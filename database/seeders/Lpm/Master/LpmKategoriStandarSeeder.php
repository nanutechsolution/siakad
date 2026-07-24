<?php

declare(strict_types=1);

namespace Database\Seeders\Lpm\Master;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LpmKategoriStandarSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('lpm_kategori_standars')->upsert([
            [
                'id' => 1,
                'nama' => 'Standar Nasional Pendidikan',
                'deskripsi' => 'Standar Nasional Pendidikan Tinggi',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'nama' => 'Standar Penelitian',
                'deskripsi' => 'Standar Penelitian',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'nama' => 'Standar Pengabdian Kepada Masyarakat',
                'deskripsi' => 'Standar PKM',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'nama' => 'Standar Tata Kelola',
                'deskripsi' => 'Standar Tata Kelola Perguruan Tinggi',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'nama' => 'Standar Kemahasiswaan',
                'deskripsi' => 'Standar Kemahasiswaan',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 6,
                'nama' => 'Standar Kerjasama',
                'deskripsi' => 'Standar Kerjasama',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['id'], [
            'nama',
            'deskripsi',
            'updated_at',
        ]);
    }
}
