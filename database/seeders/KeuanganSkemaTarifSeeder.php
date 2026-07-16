<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RefProdi;
use App\Models\RefAngkatan;
use App\Models\RefProgram;
use Illuminate\Support\Facades\DB;

class KeuanganSkemaTarifSeeder extends Seeder
{
    public function run(): void
    {
        $tahunTarget = 2026;

        // 1. Cek atau Buat Angkatan
        // Menggunakan updateOrCreate karena id_tahun adalah PK
        $angkatan = RefAngkatan::updateOrCreate(
            ['id_tahun' => $tahunTarget],
            [
                'batas_tahun_studi' => 8, // Contoh: standar 8 semester/4 tahun
                'is_active_pmb' => 1
            ]
        );

        // 2. Ambil Program Kelas (Contoh: ID 1 untuk Reguler)
        $programKelas = RefProgram::first();

        if (!$programKelas) {
            $this->command->error('Data RefProgram kosong. Harap isi data program terlebih dahulu!');
            return;
        }

        // 3. Generate Skema untuk semua Prodi
        $prodis = RefProdi::all();
        $count = 0;

        foreach ($prodis as $prodi) {
            DB::table('keuangan_skema_tarif')->updateOrInsert(
                [
                    'angkatan_id' => $angkatan->id_tahun,
                    'prodi_id' => $prodi->id,
                    'program_kelas_id' => $programKelas->id,
                ],
                [
                    'nama_skema' => "Skema Biaya {$prodi->nama_prodi} Angkatan {$tahunTarget}",
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $count++;
        }

        $this->command->info("Berhasil men-generate {$count} skema tarif untuk angkatan {$tahunTarget}.");
    }
}
