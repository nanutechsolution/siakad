<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RefJabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $jabatan = [
            // Struktural
            ['kode_jabatan' => 'REKTOR', 'nama_jabatan' => 'Rektor', 'jenis' => 'STRUKTURAL'],
            ['kode_jabatan' => 'WR1', 'nama_jabatan' => 'Wakil Rektor I (Akademik)', 'jenis' => 'STRUKTURAL'],
            ['kode_jabatan' => 'WR2', 'nama_jabatan' => 'Wakil Rektor II (Keuangan & SDM)', 'jenis' => 'STRUKTURAL'],
            ['kode_jabatan' => 'WR3', 'nama_jabatan' => 'Wakil Rektor III (Kemahasiswaan & Kerjasama)', 'jenis' => 'STRUKTURAL'],
            ['kode_jabatan' => 'DEKAN', 'nama_jabatan' => 'Dekan Fakultas', 'jenis' => 'STRUKTURAL'],
            ['kode_jabatan' => 'KAPRODI', 'nama_jabatan' => 'Ketua Program Studi', 'jenis' => 'STRUKTURAL'],
            ['kode_jabatan' => 'BARA', 'nama_jabatan' => 'BAUK', 'jenis' => 'STRUKTURAL'],
            ['kode_jabatan' => 'BAUK', 'nama_jabatan' => 'BAuk', 'jenis' => 'STRUKTURAL'],

            // Fungsional
            ['kode_jabatan' => 'DOSEN', 'nama_jabatan' => 'Dosen', 'jenis' => 'FUNGSIONAL'],
            ['kode_jabatan' => 'DOSEN_SENIOR', 'nama_jabatan' => 'Dosen Senior', 'jenis' => 'FUNGSIONAL'],
            ['kode_jabatan' => 'DOSEN_AHLI', 'nama_jabatan' => 'Dosen Ahli', 'jenis' => 'FUNGSIONAL'],
            ['kode_jabatan' => 'TENDIK', 'nama_jabatan' => 'Tenaga Kependidikan', 'jenis' => 'FUNGSIONAL'],
            ['kode_jabatan' => 'LAB_ASSISTANT', 'nama_jabatan' => 'Asisten Laboratorium', 'jenis' => 'FUNGSIONAL'],
            ['kode_jabatan' => 'PERPUSTAKAAN', 'nama_jabatan' => 'Pustakawan', 'jenis' => 'FUNGSIONAL'],
            ['kode_jabatan' => 'ADMIN', 'nama_jabatan' => 'Administrator Akademik', 'jenis' => 'FUNGSIONAL'],
        ];

        foreach ($jabatan as $j) {
            DB::table('ref_jabatan')->updateOrInsert(
                ['kode_jabatan' => $j['kode_jabatan']],
                [
                    'nama_jabatan' => $j['nama_jabatan'],
                    'jenis' => $j['jenis'],
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now
                ]
            );
        }

        $this->command->info('Seeder ref_jabatan berhasil dijalankan!');
    }
}
