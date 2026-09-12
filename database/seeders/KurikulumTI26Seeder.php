<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KurikulumTI26Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $prodi_id = 2; // Pastikan prodi_id sesuai dengan TI
        $now = Carbon::now();

        // 1. Buat / Ambil Data Master Kurikulum
        $kurikulum = DB::table('master_kurikulums')
            ->where('prodi_id', $prodi_id)
            ->where('nama_kurikulum', 'Kurikulum TI 2026')
            ->first();

        if (!$kurikulum) {
            $kurikulumId = DB::table('master_kurikulums')->insertGetId([
                'prodi_id'           => $prodi_id,
                'nama_kurikulum'     => 'Kurikulum TI 2026',
                'tahun_mulai'        => 2026,
                'id_semester_mulai'  => '20261',
                'is_active'          => 1,
                'mode_krs'           => 'PAKET',
                'jumlah_sks_lulus'   => 144,
                'jumlah_sks_wajib'   => 111, // Disesuaikan
                'jumlah_sks_pilihan' => 33,  // Disesuaikan
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        } else {
            $kurikulumId = $kurikulum->id;
        }

        // 2. Data Kurikulum Mata Kuliah (Termasuk Semester, Sifat MK, dan Prasyarat Teks)
        $kurikulum_mks = [
            // --- SEMESTER 1 ---
            ['kode_mk' => 'MKU151019001', 'semester_paket' => 1, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => []],
            ['kode_mk' => 'MKU151019003', 'semester_paket' => 1, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => []],
            ['kode_mk' => 'MKP155202101', 'semester_paket' => 1, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => []],
            ['kode_mk' => 'MKP155202302', 'semester_paket' => 1, 'sks_tatap_muka' => 2, 'sks_praktek' => 2, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => []],
            ['kode_mk' => 'MKP155202103', 'semester_paket' => 1, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => []],
            ['kode_mk' => 'MKP155202104', 'semester_paket' => 1, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => []],
            ['kode_mk' => 'MKP155202305', 'semester_paket' => 1, 'sks_tatap_muka' => 2, 'sks_praktek' => 2, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => []],
            ['kode_mk' => 'MKP155202206', 'semester_paket' => 1, 'sks_tatap_muka' => 0, 'sks_praktek' => 3, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => []],
            // --- SEMESTER 2 ---
            ['kode_mk' => 'MKU151019002', 'semester_paket' => 2, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => []],
            ['kode_mk' => 'MKU151019004', 'semester_paket' => 2, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => []],
            ['kode_mk' => 'MKP255202207', 'semester_paket' => 2, 'sks_tatap_muka' => 0, 'sks_praktek' => 3, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => ['Sistem operasi']],
            ['kode_mk' => 'MKP255202208', 'semester_paket' => 2, 'sks_tatap_muka' => 0, 'sks_praktek' => 3, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => ['Pengantar teknologi informasi']],
            ['kode_mk' => 'MKP255202209', 'semester_paket' => 2, 'sks_tatap_muka' => 0, 'sks_praktek' => 3, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => []],
            ['kode_mk' => 'MKP255202110', 'semester_paket' => 2, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => ['Matematika Diskrit']],
            ['kode_mk' => 'MKP255202311', 'semester_paket' => 2, 'sks_tatap_muka' => 2, 'sks_praktek' => 2, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => ['Algoritma dan pemograman']],
            ['kode_mk' => 'MKP255202212', 'semester_paket' => 2, 'sks_tatap_muka' => 0, 'sks_praktek' => 3, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => []],
            // --- SEMESTER 3 ---
            ['kode_mk' => 'MKU151019005', 'semester_paket' => 3, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => []],
            ['kode_mk' => 'MKP355202313', 'semester_paket' => 3, 'sks_tatap_muka' => 2, 'sks_praktek' => 2, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => ['Pemograman berorentasi objek']],
            ['kode_mk' => 'MKP355202114', 'semester_paket' => 3, 'sks_tatap_muka' => 3, 'sks_praktek' => 0, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => []],
            ['kode_mk' => 'MKP355202315', 'semester_paket' => 3, 'sks_tatap_muka' => 2, 'sks_praktek' => 2, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => ['Jaringan komputer']],
            ['kode_mk' => 'MKP355202116', 'semester_paket' => 3, 'sks_tatap_muka' => 3, 'sks_praktek' => 0, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => []],
            ['kode_mk' => 'MKP355202117', 'semester_paket' => 3, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => []],
            ['kode_mk' => 'MKP355202118', 'semester_paket' => 3, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => ['Kalkulus']],
            // --- SEMESTER 4 ---
            ['kode_mk' => 'MKU151019006', 'semester_paket' => 4, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => []],
            ['kode_mk' => 'MKP455202319', 'semester_paket' => 4, 'sks_tatap_muka' => 2, 'sks_praktek' => 2, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => []],
            ['kode_mk' => 'MKP455202320', 'semester_paket' => 4, 'sks_tatap_muka' => 2, 'sks_praktek' => 2, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => []],
            ['kode_mk' => 'MKP455202221', 'semester_paket' => 4, 'sks_tatap_muka' => 0, 'sks_praktek' => 3, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => []],
            ['kode_mk' => 'MKP455202122', 'semester_paket' => 4, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => ['Basis data']],
            ['kode_mk' => 'MKP455202123', 'semester_paket' => 4, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => []],
            ['kode_mk' => 'MKP455202124', 'semester_paket' => 4, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => ['Statistika dan Probabilitas']],
            // --- SEMESTER 5 ---
            ['kode_mk' => 'MKP555202325', 'semester_paket' => 5, 'sks_tatap_muka' => 2, 'sks_praktek' => 2, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => []],
            ['kode_mk' => 'MKP555202126', 'semester_paket' => 5, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => []],
            ['kode_mk' => 'MKP555202127', 'semester_paket' => 5, 'sks_tatap_muka' => 2, 'sks_praktek' => 0, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => []],
            ['kode_mk' => 'MKP555202328', 'semester_paket' => 5, 'sks_tatap_muka' => 2, 'sks_praktek' => 2, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => ['Rekayasa perangkat lunak']],
            ['kode_mk' => 'MKP555202129', 'semester_paket' => 5, 'sks_tatap_muka' => 3, 'sks_praktek' => 0, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => []],
            // --- SEMESTER 6 ---
            ['kode_mk' => 'MKU151019007', 'semester_paket' => 6, 'sks_tatap_muka' => 1, 'sks_praktek' => 2, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => ['Bahasa Indonesia']],
            ['kode_mk' => 'MKP655202330', 'semester_paket' => 6, 'sks_tatap_muka' => 2, 'sks_praktek' => 2, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => ['Analisis perancangan sistem']],
            ['kode_mk' => 'MKP655202331', 'semester_paket' => 6, 'sks_tatap_muka' => 2, 'sks_praktek' => 2, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => ['Pemograman web']],
            ['kode_mk' => 'MKP655202332', 'semester_paket' => 6, 'sks_tatap_muka' => 2, 'sks_praktek' => 2, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => ['Aljabar Liner dan Matriks']],
            // --- SEMESTER 7 & 8 ---
            ['kode_mk' => 'MKU151019008', 'semester_paket' => 7, 'sks_tatap_muka' => 0, 'sks_praktek' => 0, 'sks_lapangan' => 20, 'sifat_mk' => 'W', 'prasyarat' => ['Metodologi Penelitian']],
            ['kode_mk' => 'MKU151019009', 'semester_paket' => 8, 'sks_tatap_muka' => 3, 'sks_praktek' => 3, 'sks_lapangan' => 0, 'sifat_mk' => 'W', 'prasyarat' => ['Metodologi Penelitian']],

            // --- MATAKULIAH PILIHAN (Berdasarkan Gambar) ---
            ['kode_mk' => 'MPP555202101', 'semester_paket' => 5, 'sks_tatap_muka' => 3, 'sks_praktek' => 0, 'sks_lapangan' => 0, 'sifat_mk' => 'P', 'prasyarat' => ['Basis data']],
            ['kode_mk' => 'MPP65520202',  'semester_paket' => 6, 'sks_tatap_muka' => 0, 'sks_praktek' => 3, 'sks_lapangan' => 0, 'sifat_mk' => 'P', 'prasyarat' => ['Jaringan komputer']],
            ['kode_mk' => 'MPP555202103', 'semester_paket' => 5, 'sks_tatap_muka' => 3, 'sks_praktek' => 0, 'sks_lapangan' => 0, 'sifat_mk' => 'P', 'prasyarat' => ['Jaringan komputer']],
            ['kode_mk' => 'MPP655202104', 'semester_paket' => 6, 'sks_tatap_muka' => 0, 'sks_praktek' => 3, 'sks_lapangan' => 0, 'sifat_mk' => 'P', 'prasyarat' => ['Jaringan komputer']],
            ['kode_mk' => 'MPP655202305', 'semester_paket' => 6, 'sks_tatap_muka' => 2, 'sks_praktek' => 1, 'sks_lapangan' => 0, 'sifat_mk' => 'P', 'prasyarat' => ['Algoritma dan pemograman']],
            ['kode_mk' => 'MPP55202306',  'semester_paket' => 5, 'sks_tatap_muka' => 2, 'sks_praktek' => 1, 'sks_lapangan' => 0, 'sifat_mk' => 'P', 'prasyarat' => ['Struktur data']],
            ['kode_mk' => 'MPP55202307',  'semester_paket' => 5, 'sks_tatap_muka' => 2, 'sks_praktek' => 1, 'sks_lapangan' => 0, 'sifat_mk' => 'P', 'prasyarat' => ['Matematika Diskrit']],
            ['kode_mk' => 'MPP555202308', 'semester_paket' => 5, 'sks_tatap_muka' => 2, 'sks_praktek' => 1, 'sks_lapangan' => 0, 'sifat_mk' => 'P', 'prasyarat' => ['Grafika Komputer']],
            ['kode_mk' => 'MPP65202109',  'semester_paket' => 6, 'sks_tatap_muka' => 3, 'sks_praktek' => 0, 'sks_lapangan' => 0, 'sifat_mk' => 'P', 'prasyarat' => ['Basis data']],
            ['kode_mk' => 'MPP55202110',  'semester_paket' => 5, 'sks_tatap_muka' => 3, 'sks_praktek' => 0, 'sks_lapangan' => 0, 'sifat_mk' => 'P', 'prasyarat' => ['Algoritma dan pemograman']],
            ['kode_mk' => 'MPP655202111', 'semester_paket' => 6, 'sks_tatap_muka' => 3, 'sks_praktek' => 0, 'sks_lapangan' => 0, 'sifat_mk' => 'P', 'prasyarat' => ['Struktur data']],
        ];

        // 3. Masukkan ke kurikulum_mata_kuliah
        foreach ($kurikulum_mks as $mk_data) {
            $master_mk = DB::table('master_mata_kuliahs')
                ->where('prodi_id', $prodi_id)
                ->where('kode_mk', $mk_data['kode_mk'])
                ->first();

            if ($master_mk) {
                // Cek agar tidak duplikat
                $exists = DB::table('kurikulum_mata_kuliah')
                    ->where('kurikulum_id', $kurikulumId)
                    ->where('mata_kuliah_id', $master_mk->id)
                    ->first();

                if (!$exists) {
                    DB::table('kurikulum_mata_kuliah')->insert([
                        'kurikulum_id'   => $kurikulumId,
                        'mata_kuliah_id' => $master_mk->id,
                        'semester_paket' => $mk_data['semester_paket'],
                        'sks_tatap_muka' => $mk_data['sks_tatap_muka'],
                        'sks_praktek'    => $mk_data['sks_praktek'],
                        'sks_lapangan'   => $mk_data['sks_lapangan'],
                        'sifat_mk'       => $mk_data['sifat_mk'],
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ]);
                }
            }
        }

        // 4. Masukkan ke kurikulum_mk_prasyarat
        foreach ($kurikulum_mks as $mk_data) {
            if (count($mk_data['prasyarat']) > 0) {

                // Cari kurikulum_mk_id mata kuliah utama (yang mensyaratkan)
                $main_kmk = DB::table('kurikulum_mata_kuliah')
                    ->join('master_mata_kuliahs', 'kurikulum_mata_kuliah.mata_kuliah_id', '=', 'master_mata_kuliahs.id')
                    ->where('kurikulum_mata_kuliah.kurikulum_id', $kurikulumId)
                    ->where('master_mata_kuliahs.kode_mk', $mk_data['kode_mk'])
                    ->select('kurikulum_mata_kuliah.id')
                    ->first();

                if ($main_kmk) {
                    foreach ($mk_data['prasyarat'] as $nama_prasyarat) {

                        // Cari ID prasyarat dengan mencocokkan Nama Matakuliah
                        $prasyarat_kmk = DB::table('kurikulum_mata_kuliah')
                            ->join('master_mata_kuliahs', 'kurikulum_mata_kuliah.mata_kuliah_id', '=', 'master_mata_kuliahs.id')
                            ->where('kurikulum_mata_kuliah.kurikulum_id', $kurikulumId)
                            ->where('master_mata_kuliahs.nama_mk', 'LIKE', '%' . $nama_prasyarat . '%')
                            ->select('kurikulum_mata_kuliah.id')
                            ->first();

                        if ($prasyarat_kmk) {
                            $prasyaratExists = DB::table('kurikulum_mk_prasyarat')
                                ->where('kurikulum_mk_id', $main_kmk->id)
                                ->where('prasyarat_kurikulum_mk_id', $prasyarat_kmk->id)
                                ->exists();

                            if (!$prasyaratExists) {
                                DB::table('kurikulum_mk_prasyarat')->insert([
                                    'kurikulum_mk_id'           => $main_kmk->id,
                                    'min_nilai_huruf'           => 'D',
                                    'prasyarat_kurikulum_mk_id' => $prasyarat_kmk->id,
                                    'min_nilai'                 => 2.00,
                                    'logic_type'                => 'AND',
                                ]);
                            }
                        }
                    }
                }
            }
        }
    }
}
