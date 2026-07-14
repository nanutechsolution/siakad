<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\RefProdi as Prodi;
use Carbon\Carbon;

class MasterKurikulumArsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Memulai seeding Master Kurikulum ARS...');

        // 1. Ambil ID Prodi ARS
        $prodi = Prodi::where('kode_prodi_internal', 'ARS')->first();

        if (!$prodi) {
            $this->command->error("Prodi dengan kode 'ARS' tidak ditemukan. Pastikan seeder Prodi sudah dijalankan.");
            return;
        }

        // 2. Data Kurikulum ARS (Berdasarkan KPT 4.0)
        $kurikulum = [
            'prodi_id'           => $prodi->id,
            'nama_kurikulum'     => 'Kurikulum KPT 4.0 Administrasi Rumah Sakit',
            'tahun_mulai'        => 2023, // Sesuaikan dengan tahun implementasi aktual
            'id_semester_mulai'  => '20231', // Contoh: 20231 untuk Ganjil 2023/2024
            'is_active'          => 1,
            'jumlah_sks_lulus'   => 144,
            'jumlah_sks_wajib'   => 144, // Total SKS semester 1-8 dari data PDF
            'jumlah_sks_pilihan' => 0,
            'created_at'         => Carbon::now(),
            'updated_at'         => Carbon::now(),
        ];

        // 3. Eksekusi Penyimpanan (Menggunakan DB Facade agar universal dengan skema tabel)
        DB::table('master_kurikulums')->updateOrInsert(
            [
                'prodi_id'       => $kurikulum['prodi_id'],
                'nama_kurikulum' => $kurikulum['nama_kurikulum']
            ],
            $kurikulum
        );

        $this->command->info('Berhasil menambahkan Master Kurikulum untuk Administrasi Rumah Sakit.');
    }
}
