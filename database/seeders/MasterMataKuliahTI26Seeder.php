<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterMataKuliahTI26Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Sesuaikan prodi_id dengan ID Program Studi Teknik Informatika di tabel ref_prodi Anda
        $prodi_id = 2;

        $mata_kuliahs = [
            // --- DATA MATA KULIAH WAJIB (SEMESTER 1 - 8) ---
            ['kode_mk' => 'MKU151019001', 'nama_mk' => 'Pendidikan Agama', 'sks_default' => 2, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKU151019003', 'nama_mk' => 'Bahasa Indonesia', 'sks_default' => 2, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP155202101', 'nama_mk' => 'Pengantar teknologi informasi', 'sks_default' => 2, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP155202302', 'nama_mk' => 'Algoritma dan pemograman', 'sks_default' => 4, 'sks_tatap_muka' => 2, 'sks_praktek' => 2, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP155202103', 'nama_mk' => 'Matematika Diskrit', 'sks_default' => 2, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP155202104', 'nama_mk' => 'Kalkulus', 'sks_default' => 2, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP155202305', 'nama_mk' => 'Aplikasi Perkantoran', 'sks_default' => 4, 'sks_tatap_muka' => 2, 'sks_praktek' => 2, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP155202206', 'nama_mk' => 'Sistem operasi', 'sks_default' => 3, 'sks_tatap_muka' => 0, 'sks_praktek' => 3, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKU151019002', 'nama_mk' => 'Pendidikan Pancasila', 'sks_default' => 2, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKU151019004', 'nama_mk' => 'Pendidikan Anti Korupsi', 'sks_default' => 2, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP255202207', 'nama_mk' => 'Struktur data', 'sks_default' => 3, 'sks_tatap_muka' => 0, 'sks_praktek' => 3, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP255202208', 'nama_mk' => 'Organisasi dan Arsitektur Komputer', 'sks_default' => 3, 'sks_tatap_muka' => 0, 'sks_praktek' => 3, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP255202209', 'nama_mk' => 'Jaringan komputer', 'sks_default' => 3, 'sks_tatap_muka' => 0, 'sks_praktek' => 3, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP255202110', 'nama_mk' => 'Aljabar Liner dan Matriks', 'sks_default' => 2, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP255202311', 'nama_mk' => 'Pemograman berorentasi objek', 'sks_default' => 4, 'sks_tatap_muka' => 2, 'sks_praktek' => 2, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP255202212', 'nama_mk' => 'Basis data', 'sks_default' => 3, 'sks_tatap_muka' => 0, 'sks_praktek' => 3, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKU151019005', 'nama_mk' => 'Kewirausahaan', 'sks_default' => 2, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP355202313', 'nama_mk' => 'Pemograman Lanjutan', 'sks_default' => 4, 'sks_tatap_muka' => 2, 'sks_praktek' => 2, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP355202114', 'nama_mk' => 'Interaksi Manusia dan Komputer', 'sks_default' => 3, 'sks_tatap_muka' => 3, 'sks_praktek' => 0, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP355202315', 'nama_mk' => 'Pemrograman jaringan', 'sks_default' => 4, 'sks_tatap_muka' => 2, 'sks_praktek' => 2, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP355202116', 'nama_mk' => 'Rekayasa perangkat lunak', 'sks_default' => 3, 'sks_tatap_muka' => 3, 'sks_praktek' => 0, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP355202117', 'nama_mk' => 'Etika Profesi dan HaKi', 'sks_default' => 2, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP355202118', 'nama_mk' => 'Statistika dan Probabilitas', 'sks_default' => 2, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKU151019006', 'nama_mk' => 'Leadership', 'sks_default' => 2, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP455202319', 'nama_mk' => 'Pemograman web', 'sks_default' => 4, 'sks_tatap_muka' => 2, 'sks_praktek' => 2, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP455202320', 'nama_mk' => 'Sistem Informasi Geografis', 'sks_default' => 4, 'sks_tatap_muka' => 2, 'sks_praktek' => 2, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP455202221', 'nama_mk' => 'Analisis perancangan sistem', 'sks_default' => 3, 'sks_tatap_muka' => 0, 'sks_praktek' => 3, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP455202122', 'nama_mk' => 'Sistem berkas', 'sks_default' => 2, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP455202123', 'nama_mk' => 'Etika Hukum Cyber', 'sks_default' => 2, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP455202124', 'nama_mk' => 'Metode Numerik', 'sks_default' => 2, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP555202325', 'nama_mk' => 'Multimedia', 'sks_default' => 4, 'sks_tatap_muka' => 2, 'sks_praktek' => 2, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP555202126', 'nama_mk' => 'e-Bussines', 'sks_default' => 2, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP555202127', 'nama_mk' => 'Expert & Decission System', 'sks_default' => 2, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP555202328', 'nama_mk' => 'Pemograman Mobile', 'sks_default' => 4, 'sks_tatap_muka' => 2, 'sks_praktek' => 2, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP555202129', 'nama_mk' => 'Teori Adopsi Penerimaan Teknologi', 'sks_default' => 3, 'sks_tatap_muka' => 3, 'sks_praktek' => 0, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKU151019007', 'nama_mk' => 'Metodologi Penelitian', 'sks_default' => 3, 'sks_tatap_muka' => 1, 'sks_praktek' => 2, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP655202330', 'nama_mk' => 'Pemodelan dan Pegujian Sistem', 'sks_default' => 4, 'sks_tatap_muka' => 2, 'sks_praktek' => 2, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP655202331', 'nama_mk' => 'Pemrograman Web Lanjutan (Framework)', 'sks_default' => 4, 'sks_tatap_muka' => 2, 'sks_praktek' => 2, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKP655202332', 'nama_mk' => 'Grafika Komputer', 'sks_default' => 4, 'sks_tatap_muka' => 2, 'sks_praktek' => 2, 'sks_lapangan' => 0],
            ['kode_mk' => 'MKU151019008', 'nama_mk' => 'Magang', 'sks_default' => 20, 'sks_tatap_muka' => 0, 'sks_praktek' => 0, 'sks_lapangan' => 20],
            ['kode_mk' => 'MKU151019009', 'nama_mk' => 'Tugas Akhir', 'sks_default' => 6, 'sks_tatap_muka' => 3, 'sks_praktek' => 3, 'sks_lapangan' => 0],

            // --- DATA MATAKULIAH PILIHAN ---
            ['kode_mk' => 'MPP555202101', 'nama_mk' => 'Data Mining', 'sks_default' => 3, 'sks_tatap_muka' => 3, 'sks_praktek' => 0, 'sks_lapangan' => 0],
            ['kode_mk' => 'MPP65520202',  'nama_mk' => 'Cloud Computing', 'sks_default' => 3, 'sks_tatap_muka' => 0, 'sks_praktek' => 3, 'sks_lapangan' => 0],
            ['kode_mk' => 'MPP555202103', 'nama_mk' => 'Sistem Terdistribusi', 'sks_default' => 3, 'sks_tatap_muka' => 3, 'sks_praktek' => 0, 'sks_lapangan' => 0],
            ['kode_mk' => 'MPP655202104', 'nama_mk' => 'Keamanan Jaringan', 'sks_default' => 3, 'sks_tatap_muka' => 0, 'sks_praktek' => 3, 'sks_lapangan' => 0],
            ['kode_mk' => 'MPP655202305', 'nama_mk' => 'Artificial Intelegensi (AI)', 'sks_default' => 3, 'sks_tatap_muka' => 2, 'sks_praktek' => 1, 'sks_lapangan' => 0],
            ['kode_mk' => 'MPP55202306',  'nama_mk' => 'Machine Learning', 'sks_default' => 3, 'sks_tatap_muka' => 2, 'sks_praktek' => 1, 'sks_lapangan' => 0],
            ['kode_mk' => 'MPP55202307',  'nama_mk' => 'Criptography', 'sks_default' => 3, 'sks_tatap_muka' => 2, 'sks_praktek' => 1, 'sks_lapangan' => 0],
            ['kode_mk' => 'MPP555202308', 'nama_mk' => 'Computer Vision', 'sks_default' => 3, 'sks_tatap_muka' => 2, 'sks_praktek' => 1, 'sks_lapangan' => 0],
            ['kode_mk' => 'MPP65202109',  'nama_mk' => 'Bussines Intelegensi dan Big Data', 'sks_default' => 3, 'sks_tatap_muka' => 3, 'sks_praktek' => 0, 'sks_lapangan' => 0],
            ['kode_mk' => 'MPP55202110',  'nama_mk' => 'Kecerdasan Komputasi', 'sks_default' => 3, 'sks_tatap_muka' => 3, 'sks_praktek' => 0, 'sks_lapangan' => 0],
            ['kode_mk' => 'MPP655202111', 'nama_mk' => 'Block Chain', 'sks_default' => 3, 'sks_tatap_muka' => 3, 'sks_praktek' => 0, 'sks_lapangan' => 0],
        ];

        $now = Carbon::now();

        foreach ($mata_kuliahs as $mk) {
            // Cek jika mata kuliah sudah ada berdasarkan kombinasi prodi_id dan kode_mk
            $exists = DB::table('master_mata_kuliahs')
                ->where('prodi_id', $prodi_id)
                ->where('kode_mk', $mk['kode_mk'])
                ->exists();

            // Jika belum ada, lakukan insert (skip jika sudah ada)
            if (!$exists) {
                DB::table('master_mata_kuliahs')->insert([
                    'prodi_id'       => $prodi_id,
                    'kode_mk'        => $mk['kode_mk'],
                    'nama_mk'        => $mk['nama_mk'],
                    'sks_default'    => $mk['sks_default'],
                    'sks_tatap_muka' => $mk['sks_tatap_muka'],
                    'sks_praktek'    => $mk['sks_praktek'],
                    'sks_lapangan'   => $mk['sks_lapangan'],
                    'jenis_mk'       => 'A',         // Sesuai default DB
                    'activity_type'  => 'REGULAR',   // Sesuai default DB
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
            }
        }
    }
}
