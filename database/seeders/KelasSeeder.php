<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        // 1. Ambil data referensi yang dibutuhkan[cite: 1]
        // Pastikan ReferenceDataSeeder sudah dijalankan sebelumnya
        $prodiIF = DB::table('ref_prodi')->where('kode_prodi_internal', 'TI')->first();
        $prodiSI = DB::table('ref_prodi')->where('kode_prodi_internal', 'MI')->first();
        $programReguler = DB::table('ref_program')->where('kode_internal', 'REG')->first();
        $angkatan = DB::table('ref_angkatan')->orderBy('id_tahun', 'desc')->first();

        // Validasi jika data referensi belum ada
        if (!$prodiIF || !$programReguler || !$angkatan) {
            $this->command->warn('Data Prodi, Program, atau Angkatan belum ada. Silakan jalankan ReferenceDataSeeder terlebih dahulu.');
            return;
        }

        // 2. Siapkan data kelas yang akan disisipkan
        $dataKelas = [
            // Kelas untuk Prodi Teknik Informatika (IF)
            [
                'nama_kelas'  => 'IF-A',
                'prodi_id'    => $prodiIF->id,
                'program_id'  => $programReguler->id,
                'angkatan_id' => $angkatan->id_tahun,
                'kapasitas'   => 40,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'nama_kelas'  => 'IF-B',
                'prodi_id'    => $prodiIF->id,
                'program_id'  => $programReguler->id,
                'angkatan_id' => $angkatan->id_tahun,
                'kapasitas'   => 40,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];

        // Tambahkan kelas untuk Sistem Informasi (SI) jika prodinya ada
        if ($prodiSI) {
            $dataKelas[] = [
                'nama_kelas'  => 'SI-A',
                'prodi_id'    => $prodiSI->id,
                'program_id'  => $programReguler->id,
                'angkatan_id' => $angkatan->id_tahun,
                'kapasitas'   => 35,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        // 3. Eksekusi insert data ke dalam tabel 'kelas'[cite: 1]
        foreach ($dataKelas as $kelas) {
            // Menggunakan updateOrInsert untuk menghindari error constraint Unique[cite: 1]
            DB::table('kelas')->updateOrInsert(
                [
                    'nama_kelas'  => $kelas['nama_kelas'],
                    'prodi_id'    => $kelas['prodi_id'],
                    'program_id'  => $kelas['program_id'],
                    'angkatan_id' => $kelas['angkatan_id'],
                ],
                [
                    'kapasitas'  => $kelas['kapasitas'],
                    'created_at' => $kelas['created_at'],
                    'updated_at' => $kelas['updated_at'],
                ]
            );
        }

        $this->command->info('Tabel kelas berhasil diisi (seeded)!');
    }
}
