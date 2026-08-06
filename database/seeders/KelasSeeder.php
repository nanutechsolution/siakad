<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\RefAngkatan;
use App\Models\RefProdi;
use App\Models\RefProgram;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        $prodi = RefProdi::where('kode_prodi_internal', 'TI')->firstOrFail();

        $programReguler = RefProgram::where('kode_internal', 'REG')->firstOrFail();
        $programEkstensi = RefProgram::where('kode_internal', 'EKS')->firstOrFail();

        $angkatan2024 = RefAngkatan::where('id_tahun', 2024)->firstOrFail();
        $angkatan2025 = RefAngkatan::where('id_tahun', 2025)->firstOrFail();

        // ===============================
        // REGULER 2024
        // ===============================
        foreach (['A', 'B', 'C', 'D', 'E'] as $kelas) {
            Kelas::updateOrCreate(
                [
                    'nama_kelas'  => $kelas,
                    'prodi_id'    => $prodi->id,
                    'program_id'  => $programReguler->id,
                    'angkatan_id' => $angkatan2024->id_tahun,
                ],
                [
                    'kapasitas' => 40,
                ]
            );
        }

        // ===============================
        // EKSTENSI 2024
        // ===============================
        Kelas::updateOrCreate(
            [
                'nama_kelas'  => 'EKSTENSI-WKB',
                'prodi_id'    => $prodi->id,
                'program_id'  => $programEkstensi->id,
                'angkatan_id' => $angkatan2024->id_tahun,
            ],
            [
                'kapasitas' => 40,
            ]
        );

        // ===============================
        // REGULER 2025
        // ===============================
        foreach (['A', 'B', 'C', 'D', 'E'] as $kelas) {
            Kelas::updateOrCreate(
                [
                    'nama_kelas'  => $kelas,
                    'prodi_id'    => $prodi->id,
                    'program_id'  => $programReguler->id,
                    'angkatan_id' => $angkatan2025->id,
                ],
                [
                    'kapasitas' => 40,
                ]
            );
        }
    }
}
