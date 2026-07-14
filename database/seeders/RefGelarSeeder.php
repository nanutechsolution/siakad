<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RefGelarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * TAHAP 1: Membersihkan Data Lama
         * Kita matikan foreign key checks sementara agar truncate tidak error 
         * jika tabel ini direferensikan oleh tabel lain (seperti trx_person_gelar).
         */
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('ref_gelar')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        /**
         * TAHAP 2: Konfigurasi Case Sensitivity
         * Mengubah Collation kolom 'kode' menjadi binary agar 'dr.' dan 'Dr.' 
         * dianggap sebagai entitas yang berbeda oleh MySQL.
         */
        DB::statement('ALTER TABLE ref_gelar MODIFY kode VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL');

        $gelars = [
            // ==========================================
            // GELAR DEPAN (Posisi: DEPAN)
            // ==========================================
            ['kode' => 'dr.', 'nama' => 'Dokter', 'posisi' => 'DEPAN', 'jenjang' => 'PROFESI'],
            ['kode' => 'drg.', 'nama' => 'Dokter Gigi', 'posisi' => 'DEPAN', 'jenjang' => 'PROFESI'],
            ['kode' => 'Ir.', 'nama' => 'Insinyur', 'posisi' => 'DEPAN', 'jenjang' => 'PROFESI'],
            ['kode' => 'Ns.', 'nama' => 'Ners', 'posisi' => 'DEPAN', 'jenjang' => 'PROFESI'],
            ['kode' => 'Prof.', 'nama' => 'Profesor', 'posisi' => 'DEPAN', 'jenjang' => 'PROFESI'],
            ['kode' => 'Drs.', 'nama' => 'Dokterandus', 'posisi' => 'DEPAN', 'jenjang' => 'S1'],
            ['kode' => 'Dra.', 'nama' => 'Dokteranda', 'posisi' => 'DEPAN', 'jenjang' => 'S1'],
            ['kode' => 'apt.', 'nama' => 'Apoteker', 'posisi' => 'DEPAN', 'jenjang' => 'PROFESI'],

            // ==========================================
            // GELAR BELAKANG (Posisi: BELAKANG)
            // ==========================================
            
            // Diploma 3 (D3)
            ['kode' => 'A.Md.', 'nama' => 'Ahli Madya', 'posisi' => 'BELAKANG', 'jenjang' => 'D3'],
            ['kode' => 'A.Md.Mars', 'nama' => 'Ahli Madya Manajemen Rumah Sakit', 'posisi' => 'BELAKANG', 'jenjang' => 'D3'],
            ['kode' => 'A.Md.Kep.', 'nama' => 'Ahli Madya Keperawatan', 'posisi' => 'BELAKANG', 'jenjang' => 'D3'],
            ['kode' => 'A.Md.Keb.', 'nama' => 'Ahli Madya Kebidanan', 'posisi' => 'BELAKANG', 'jenjang' => 'D3'],
            ['kode' => 'A.Md.Farm.', 'nama' => 'Ahli Madya Farmasi', 'posisi' => 'BELAKANG', 'jenjang' => 'D3'],
            ['kode' => 'A.Md.Gz.', 'nama' => 'Ahli Madya Gizi', 'posisi' => 'BELAKANG', 'jenjang' => 'D3'],
            ['kode' => 'A.Md.Kom.', 'nama' => 'Ahli Madya Komputer', 'posisi' => 'BELAKANG', 'jenjang' => 'D3'],
            ['kode' => 'A.Md.Ak.', 'nama' => 'Ahli Madya Akuntansi', 'posisi' => 'BELAKANG', 'jenjang' => 'D3'],

            // Sarjana Terapan (D4)
            ['kode' => 'S.Tr.Keb.', 'nama' => 'Sarjana Terapan Kebidanan', 'posisi' => 'BELAKANG', 'jenjang' => 'D4'],
            ['kode' => 'S.Tr.Kep.', 'nama' => 'Sarjana Terapan Keperawatan', 'posisi' => 'BELAKANG', 'jenjang' => 'D4'],
            ['kode' => 'S.Tr.Kom.', 'nama' => 'Sarjana Terapan Komputer', 'posisi' => 'BELAKANG', 'jenjang' => 'D4'],
            ['kode' => 'S.Tr.T.', 'nama' => 'Sarjana Terapan Teknik', 'posisi' => 'BELAKANG', 'jenjang' => 'D4'],

            // Sarjana (S1)
            ['kode' => 'S.Kom.', 'nama' => 'Sarjana Komputer', 'posisi' => 'BELAKANG', 'jenjang' => 'S1'],
            ['kode' => 'S.T.', 'nama' => 'Sarjana Teknik', 'posisi' => 'BELAKANG', 'jenjang' => 'S1'],
            ['kode' => 'S.Pd.', 'nama' => 'Sarjana Pendidikan', 'posisi' => 'BELAKANG', 'jenjang' => 'S1'],
            ['kode' => 'S.H.', 'nama' => 'Sarjana Hukum', 'posisi' => 'BELAKANG', 'jenjang' => 'S1'],
            ['kode' => 'S.E.', 'nama' => 'Sarjana Ekonomi', 'posisi' => 'BELAKANG', 'jenjang' => 'S1'],
            ['kode' => 'S.Si.', 'nama' => 'Sarjana Sains', 'posisi' => 'BELAKANG', 'jenjang' => 'S1'],
            ['kode' => 'S.Sos.', 'nama' => 'Sarjana Ilmu Sosial', 'posisi' => 'BELAKANG', 'jenjang' => 'S1'],
            ['kode' => 'S.Ak.', 'nama' => 'Sarjana Akuntansi', 'posisi' => 'BELAKANG', 'jenjang' => 'S1'],
            ['kode' => 'S.Kep.', 'nama' => 'Sarjana Keperawatan', 'posisi' => 'BELAKANG', 'jenjang' => 'S1'],
            ['kode' => 'S.I.P.', 'nama' => 'Sarjana Ilmu Pemerintahan', 'posisi' => 'BELAKANG', 'jenjang' => 'S1'],
            ['kode' => 'S.Gz.', 'nama' => 'Sarjana Gizi', 'posisi' => 'BELAKANG', 'jenjang' => 'S1'],

            // Magister (S2)
            ['kode' => 'M.Kom.', 'nama' => 'Magister Komputer', 'posisi' => 'BELAKANG', 'jenjang' => 'S2'],
            ['kode' => 'M.T.', 'nama' => 'Magister Teknik', 'posisi' => 'BELAKANG', 'jenjang' => 'S2'],
            ['kode' => 'M.Pd.', 'nama' => 'Magister Pendidikan', 'posisi' => 'BELAKANG', 'jenjang' => 'S2'],
            ['kode' => 'M.H.', 'nama' => 'Magister Hukum', 'posisi' => 'BELAKANG', 'jenjang' => 'S2'],
            ['kode' => 'M.M.', 'nama' => 'Magister Manajemen', 'posisi' => 'BELAKANG', 'jenjang' => 'S2'],
            ['kode' => 'M.Si.', 'nama' => 'Magister Sains', 'posisi' => 'BELAKANG', 'jenjang' => 'S2'],
            ['kode' => 'M.Ak.', 'nama' => 'Magister Akuntansi', 'posisi' => 'BELAKANG', 'jenjang' => 'S2'],
            ['kode' => 'M.Kep.', 'nama' => 'Magister Keperawatan', 'posisi' => 'BELAKANG', 'jenjang' => 'S2'],
            ['kode' => 'MARS', 'nama' => 'Magister Administrasi Rumah Sakit', 'posisi' => 'BELAKANG', 'jenjang' => 'S2'],

            // Doktor (S3)
            ['kode' => 'Dr.', 'nama' => 'Doktor', 'posisi' => 'DEPAN', 'jenjang' => 'S3'],
            ['kode' => 'Ph.D.', 'nama' => 'Doctor of Philosophy', 'posisi' => 'BELAKANG', 'jenjang' => 'S3'],
            ['kode' => 'Ed.D.', 'nama' => 'Doctor of Education', 'posisi' => 'BELAKANG', 'jenjang' => 'S3'],
        ];

        foreach ($gelars as $gelar) {
            /**
             * Memasukkan data ke tabel. Karena sudah di-truncate di awal, 
             * kita bisa menggunakan insert atau updateOrInsert untuk keamanan tambahan.
             */
            DB::table('ref_gelar')->updateOrInsert(
                ['kode' => $gelar['kode']],
                [
                    'nama'       => $gelar['nama'],
                    'posisi'     => $gelar['posisi'],
                    'jenjang'    => $gelar['jenjang'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $this->command->info('Tabel dikosongkan dan Master Gelar berhasil disinkronkan (Termasuk dr., MARS, dan Dr.).');
    }
}