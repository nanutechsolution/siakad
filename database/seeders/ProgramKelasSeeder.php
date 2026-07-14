<?php

namespace Database\Seeders;

use App\Models\RefProgram;
use Illuminate\Database\Seeder;

class ProgramKelasSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk Program Kelas.
     */
    public function run(): void
    {
        $programs = [
            [
                'kode_internal' => 'REG',
                'nama_program' => 'Reguler',
                'is_active' => true,
                'deskripsi' => 'Program perkuliahan jam kerja (Pagi/Siang).'
            ],
            [
                'kode_internal' => 'EKS',
                'nama_program' => 'Ekstensi',
                'is_active' => true,
                'deskripsi' => 'Program perkuliahan luar jam kerja (Malam) untuk karyawan.'
            ],
        ];

        foreach ($programs as $prog) {
            RefProgram::updateOrCreate(
                ['kode_internal' => $prog['kode_internal']],
                [
                    'nama_program' => $prog['nama_program'],
                    'is_active' => $prog['is_active'],
                    'deskripsi' => $prog['deskripsi'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $this->command->info('Seeder Program Kelas (Reguler & Ekstensi) berhasil dijalankan.');
    }
}
