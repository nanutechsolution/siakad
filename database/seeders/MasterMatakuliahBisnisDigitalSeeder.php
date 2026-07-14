<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MasterMatakuliahBisnisDigitalSeeder extends Seeder
{
    /**
     * Seeder untuk Master Mata Kuliah S1 Bisnis Digital (BD)
     * TERINTEGRASI DENGAN KURIKULUM & PRASYARAT
     * BERDASARKAN DRAF EXCEL "RANCANG KURIKULUM PRODI BD 2025.xlsx"
     */
    public function run(): void
    {
        $this->command->info('Memulai seeding Kurikulum & Mata Kuliah Bisnis Digital...');

        // 1. Ambil Prodi BD
        $prodi = DB::table('ref_prodi')->where('kode_prodi_internal', 'BD')->first();

        if (!$prodi) {
            $this->command->error("Prodi dengan kode 'BD' tidak ditemukan di tabel ref_prodi.");
            return;
        }

        $now = Carbon::now();

        // 2. Buat atau Update Master Kurikulum
        DB::table('master_kurikulums')->updateOrInsert(
            [
                'prodi_id'       => $prodi->id,
                'nama_kurikulum' => 'Kurikulum MBKM Bisnis Digital 2025',
            ],
            [
                'tahun_mulai'        => 2025,
                'is_active'          => 1,
                'jumlah_sks_lulus'   => 145, // Total SKS berdasarkan Excel
                'jumlah_sks_wajib'   => 137, // Asumsi 145 dikurangi 8 SKS Pilihan
                'jumlah_sks_pilihan' => 8,   // (Smt 3: 4 SKS, Smt 5: 4 SKS Pilihan Konsentrasi)
                'created_at'         => $now,
                'updated_at'         => $now,
            ]
        );

        // Ambil ID Kurikulum yang baru saja dibuat/diupdate
        $kurikulum = DB::table('master_kurikulums')
            ->where('prodi_id', $prodi->id)
            ->where('nama_kurikulum', 'Kurikulum MBKM Bisnis Digital 2025')
            ->first();

        // 3. Daftar Mata Kuliah Berdasarkan Draf Excel
        $courses = [
            // --- SEMESTER 1 ---
            ['sem' => 1, 'kode' => 'BD1612092001', 'nama' => 'Pendidikan Agama', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'A', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 1, 'kode' => 'BD1612092002', 'nama' => 'Pendidikan Pancasila', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'A', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 1, 'kode' => 'BD1612092003', 'nama' => 'Pendidikan Anti Korupsi', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 1, 'kode' => 'BD1612092004', 'nama' => 'Pendidikan Bahasa Indonesia', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'A', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 1, 'kode' => 'BD1612092005', 'nama' => 'Pendidikan Bahasa Inggris', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 1, 'kode' => 'BD1612092006', 'nama' => 'Pengantar Bisnis Digital', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 1, 'kode' => 'BD1612092007', 'nama' => 'Pengantar Akuntansi', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 1, 'kode' => 'BD1612092008', 'nama' => 'Pengantar Manajemen Bisnis', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 1, 'kode' => 'BD1612092009', 'nama' => 'Pengantar Teknologi Informasi', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 1, 'kode' => 'BD1612092010', 'nama' => 'Pemasaran Digital', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 1, 'kode' => 'BD1612093011', 'nama' => 'Informatika dan Komputer Dasar', 'tm' => 0, 'p' => 3, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],

            // --- SEMESTER 2 ---
            ['sem' => 2, 'kode' => 'BD1612092012', 'nama' => 'Sistem Basis Data', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 2, 'kode' => 'BD1612092013', 'nama' => 'Pengantar Ekonomi', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 2, 'kode' => 'BD1612092014', 'nama' => 'Komunikasi Bisnis Digital', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 2, 'kode' => 'BD1612092015', 'nama' => 'Perancangan dan Pengembangan Produk', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 2, 'kode' => 'BD1612092016', 'nama' => 'Perencanaan Bisnsis Digital', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 2, 'kode' => 'BD1612092017', 'nama' => 'Manajemen Pemasaran', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 2, 'kode' => 'BD1612092018', 'nama' => 'Akuntansi Biaya dan Manajerial', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 2, 'kode' => 'BD1612092019', 'nama' => 'Statistik Bisnis', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 2, 'kode' => 'BD1612092020', 'nama' => 'Sistem Infromasi Manajemen', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 2, 'kode' => 'BD1612092021', 'nama' => 'Hukum Dan Etika Bisnis', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 2, 'kode' => 'BD1612093022', 'nama' => 'Desain Grafis I', 'tm' => 0, 'p' => 3, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],

            // --- SEMESTER 3 ---
            ['sem' => 3, 'kode' => 'BD1612092023', 'nama' => 'Analisa Desain Sistem', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 3, 'kode' => 'BD1612092024', 'nama' => 'Etika Digital', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 3, 'kode' => 'BD1612092025', 'nama' => 'Manajemen Bisnis Digital', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 3, 'kode' => 'BD1612092026', 'nama' => 'Sistem Informasi Akuntansi', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 3, 'kode' => 'BD1612092027', 'nama' => 'Digipreneur', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 3, 'kode' => 'BD1612092028', 'nama' => 'e-CRM (e-Customers Relation Management)', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 3, 'kode' => 'BD1612092029', 'nama' => 'ERP (Enterprise Resource Planning)', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 3, 'kode' => 'BD1612092030', 'nama' => 'Pengenalan Big Data', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 3, 'kode' => 'BD1612092031', 'nama' => 'Desain Grafis ll', 'tm' => 0, 'p' => 2, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 3, 'kode' => 'BD1612092032', 'nama' => 'Mata Kuliah Pilihan', 'tm' => 4, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'P', 'prasyarat' => []],

            // --- SEMESTER 4 ---
            ['sem' => 4, 'kode' => 'BD1612092033', 'nama' => 'e-Business', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 4, 'kode' => 'BD1612092034', 'nama' => 'Cyber Security', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 4, 'kode' => 'BD1612092035', 'nama' => 'SCM (Supply Chain Management)', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 4, 'kode' => 'BD1612092036', 'nama' => 'Analisis Perilaku Konsumen dan Pasar', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 4, 'kode' => 'BD1612092037', 'nama' => 'Studi Kelayakan Bisnis', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 4, 'kode' => 'BD1612092038', 'nama' => 'Manajemen Risiko', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 4, 'kode' => 'BD1612092039', 'nama' => 'Analisis Bisnis dan Big Data', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 4, 'kode' => 'BD1612092040', 'nama' => 'Pembelajaran Mesin dan Intelegensi Arfisial', 'tm' => 0, 'p' => 2, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],

            // --- SEMESTER 5 ---
            ['sem' => 5, 'kode' => 'BD1612092041', 'nama' => 'Perdagangan Elektronik', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 5, 'kode' => 'BD1612093042', 'nama' => 'Bisnis UMKM dan Start-Up', 'tm' => 3, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 5, 'kode' => 'BD1612092043', 'nama' => 'Sistem Database', 'tm' => 0, 'p' => 2, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 5, 'kode' => 'BD1612093044', 'nama' => 'Jejaring dan Keamanan Data Bisnis', 'tm' => 0, 'p' => 3, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 5, 'kode' => 'BD1612092045', 'nama' => 'Studi Kelayakan Bisnis', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 5, 'kode' => 'BD1612092046', 'nama' => 'Kewirausahaan dan Inovasi', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 5, 'kode' => 'BD1612092047', 'nama' => 'Manajemen Operasi', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 5, 'kode' => 'BD1612092048', 'nama' => 'Mata Kuliah Pilihan Konsentrasi 1', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'P', 'prasyarat' => []],
            ['sem' => 5, 'kode' => 'BD1612092049', 'nama' => 'Mata Kuliah Pilihan Konsentrasi 2', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'P', 'prasyarat' => []],

            // --- SEMESTER 6 ---
            ['sem' => 6, 'kode' => 'BD1612092050', 'nama' => 'Business Process Management', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 6, 'kode' => 'BD1612093051', 'nama' => 'Perpajakan Digital', 'tm' => 3, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 6, 'kode' => 'BD1612094052', 'nama' => 'Digipreneur Lanjut', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 6, 'kode' => 'BD1612095053', 'nama' => 'Manajemen Kuantitatif Untuk Bisnis', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 6, 'kode' => 'BD1612096054', 'nama' => 'Praktikum Manajemen Kuantitatif Untuk Bisnis', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 6, 'kode' => 'BD1612097055', 'nama' => 'Metodologi Penelitian Bisnis', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 6, 'kode' => 'BD1612098056', 'nama' => 'Praktek Bisnis Digital', 'tm' => 3, 'p' => 0, 'l' => 0, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],

            // --- SEMESTER 7 ---
            ['sem' => 7, 'kode' => 'BD1612092057', 'nama' => 'PKL/Magang/KKN', 'tm' => 0, 'p' => 0, 'l' => 20, 'jenis' => 'B', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 7, 'kode' => 'BD1612092058', 'nama' => 'Skripsi', 'tm' => 0, 'p' => 0, 'l' => 6, 'jenis' => 'D', 'sifat' => 'W', 'prasyarat' => []],
        ];

        DB::beginTransaction();
        try {
            $kurikulumMkIds = [];

            // 4. Lakukan Insert/Update ke Master Mata Kuliah & Kurikulum Mata Kuliah
            foreach ($courses as $course) {
                $totalSks = $course['tm'] + $course['p'] + $course['l'];
                
                // Gunakan activity_type THESIS jika namanya Skripsi
                // INTERNSHIP
                $activityType = 'REGULAR';
                if ($course['nama'] === 'Skripsi') {
                    $activityType = 'THESIS';
                } elseif ($course['nama'] === 'PKL/Magang/KKN') {
                    $activityType = 'INTERNSHIP';
                }

                // Insert/Update Master Mata Kuliah
                DB::table('master_mata_kuliahs')->updateOrInsert(
                    [
                        'prodi_id' => $prodi->id,
                        'kode_mk'  => $course['kode'],
                    ],
                    [
                        'nama_mk'        => $course['nama'],
                        'sks_default'    => $totalSks,
                        'sks_tatap_muka' => $course['tm'],
                        'sks_praktek'    => $course['p'],
                        'sks_lapangan'   => $course['l'],
                        'jenis_mk'       => $course['jenis'],
                        'activity_type'  => $activityType,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ]
                );

                // Fetch Master Mata Kuliah ID
                $mk = DB::table('master_mata_kuliahs')
                    ->where('prodi_id', $prodi->id)
                    ->where('kode_mk', $course['kode'])
                    ->first();

                // Insert/Update Kurikulum Mata Kuliah
                DB::table('kurikulum_mata_kuliah')->updateOrInsert(
                    [
                        'kurikulum_id'   => $kurikulum->id,
                        'mata_kuliah_id' => $mk->id,
                    ],
                    [
                        'semester_paket' => $course['sem'],
                        'sks_tatap_muka' => $course['tm'],
                        'sks_praktek'    => $course['p'],
                        'sks_lapangan'   => $course['l'],
                        'sifat_mk'       => $course['sifat'],
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ]
                );

                // Fetch Kurikulum MK ID dan simpan ke dictionary
                $kurikulumMk = DB::table('kurikulum_mata_kuliah')
                    ->where('kurikulum_id', $kurikulum->id)
                    ->where('mata_kuliah_id', $mk->id)
                    ->first();

                $kurikulumMkIds[$course['kode']] = $kurikulumMk->id;
            }

            // 5. Pemetaan Prasyarat Berdasarkan Dictionary 
            // (Saat ini array masih kosong, namun logic ini siap pakai jika nanti dibutuhkan)
            foreach ($courses as $course) {
                if (!empty($course['prasyarat'])) {
                    $targetKurikulumMkId = $kurikulumMkIds[$course['kode']];

                    foreach ($course['prasyarat'] as $kodePrasyarat) {
                        if (isset($kurikulumMkIds[$kodePrasyarat])) {
                            $prasyaratId = $kurikulumMkIds[$kodePrasyarat];

                            DB::table('kurikulum_mk_prasyarat')->updateOrInsert(
                                [
                                    'kurikulum_mk_id'           => $targetKurikulumMkId,
                                    'prasyarat_kurikulum_mk_id' => $prasyaratId,
                                ],
                                [
                                    'min_nilai_huruf' => 'C',
                                    'min_nilai'       => 2.00,
                                    'logic_type'      => 'AND',
                                ]
                            );
                        }
                    }
                }
            }

            DB::commit();
            $this->command->info("Seeding Kurikulum, Mata Kuliah, dan Prasyarat Bisnis Digital berhasil diselesaikan!");

        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error("Terjadi kesalahan saat memproses data: " . $e->getMessage());
        }
    }
}