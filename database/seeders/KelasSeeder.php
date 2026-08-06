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

        $program = RefProgram::where('kode_internal', 'REG')->firstOrFail();

        $angkatan = RefAngkatan::where('id_tahun', 2024)->firstOrFail();
        foreach (range('A', 'E') as $kelas) {
            Kelas::updateOrCreate(
                [
                    'nama_kelas' => $kelas,
                    'prodi_id' => $prodi->id,
                    'program_id' => $program->id,
                    'angkatan_id' => $angkatan->id_tahun,
                ],
                [
                    'kapasitas' => 40,
                ]
            );
        }
    }
}
