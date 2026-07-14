<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MasterMatakuliahMISeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Memulai seeding Kurikulum & Mata Kuliah Manajemen Informatika...');

        // 1. Ambil Prodi MI
        $prodi = DB::table('ref_prodi')->where('kode_prodi_internal', 'MI')->first();

        if (!$prodi) {
            $this->command->error("Prodi dengan kode 'MI' tidak ditemukan di tabel ref_prodi.");
            return;
        }

        $now = Carbon::now();

        // 2. Buat atau Update Master Kurikulum
        DB::table('master_kurikulums')->updateOrInsert(
            [
                'prodi_id'       => $prodi->id,
                'nama_kurikulum' => 'Kurikulum MBKM MI 2024',
            ],
            [
                'tahun_mulai'        => 2024,
                'is_active'          => 1,
                'jumlah_sks_lulus'   => 120, // 120 SKS sesuai total di bagan (23+23+24+24+20+6)
                'jumlah_sks_wajib'   => 100, // Asumsi 100 Wajib, 20 Pilihan
                'jumlah_sks_pilihan' => 20,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]
        );

        // Ambil ID Kurikulum yang baru saja dibuat/diupdate
        $kurikulum = DB::table('master_kurikulums')
            ->where('prodi_id', $prodi->id)
            ->where('nama_kurikulum', 'Kurikulum MBKM MI 2024')
            ->first();

        // 3. Data Master Mata Kuliah beserta Mapping Kurikulum & Prasyarat
        $courses = [
            // SEMESTER 1
            ['sem' => 1, 'kode' => 'MI1110001', 'nama' => 'Pendidikan Agama', 'sks' => 2, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 1, 'kode' => 'MI1110002', 'nama' => 'Bahasa Indonesia', 'sks' => 2, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 1, 'kode' => 'MI1110003', 'nama' => 'Pengantar Teknologi Informatika', 'sks' => 2, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 1, 'kode' => 'MI1110004', 'nama' => 'Akuntansi I', 'sks' => 2, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 1, 'kode' => 'MI1110005', 'nama' => 'Algoritma dan Struktur Data', 'sks' => 3, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 1, 'kode' => 'MI1110006', 'nama' => 'Dasar Manajemen Bisnis', 'sks' => 2, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 1, 'kode' => 'MI1110007', 'nama' => 'Aplikasi Perkantoran I', 'sks' => 3, 'ket' => 'Praktek', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 1, 'kode' => 'MI1110008', 'nama' => 'Bahasa Inggris I', 'sks' => 2, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 1, 'kode' => 'MI1110009', 'nama' => 'Kalkulus', 'sks' => 2, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 1, 'kode' => 'MI1110010', 'nama' => 'Dasar Pemrograman (Bahasa C)', 'sks' => 3, 'ket' => 'Praktek', 'sifat' => 'W', 'prasyarat' => []],

            // SEMESTER 2
            ['sem' => 2, 'kode' => 'MI2110011', 'nama' => 'Pendidikan Pancasila', 'sks' => 2, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 2, 'kode' => 'MI2110012', 'nama' => 'Aplikasi dan Sistem Multimedia', 'sks' => 2, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 2, 'kode' => 'MI2110013', 'nama' => 'Statistika Manajemen dan Bisnis', 'sks' => 2, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => ['MI1110006']], // SMT 1/6
            ['sem' => 2, 'kode' => 'MI2110014', 'nama' => 'Sistem Operasi', 'sks' => 3, 'ket' => 'Praktek', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 2, 'kode' => 'MI2110015', 'nama' => 'Akuntansi II', 'sks' => 2, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => ['MI1110004']], // SMT 1/4
            ['sem' => 2, 'kode' => 'MI2110016', 'nama' => 'Konsep Sistem Informasi', 'sks' => 3, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 2, 'kode' => 'MI2110017', 'nama' => 'Sistem Basis Data', 'sks' => 3, 'ket' => 'Praktek', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 2, 'kode' => 'MI2110018', 'nama' => 'Bahasa Inggris Manajemen', 'sks' => 2, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => ['MI1110008']], // SMT 1/8
            ['sem' => 2, 'kode' => 'MI2110019', 'nama' => 'Perpajakan', 'sks' => 3, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 2, 'kode' => 'MI2110020', 'nama' => 'Entrepreneurship', 'sks' => 2, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => []],

            // SEMESTER 3
            ['sem' => 3, 'kode' => 'MI3110021', 'nama' => 'Pendidikan Anti Korupsi', 'sks' => 2, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 3, 'kode' => 'MI3110022', 'nama' => 'E-Commerce', 'sks' => 3, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 3, 'kode' => 'MI3110023', 'nama' => 'Etika Hukum Cyber', 'sks' => 2, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 3, 'kode' => 'MI3110024', 'nama' => 'Administrasi Jaringan', 'sks' => 3, 'ket' => 'Praktek', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 3, 'kode' => 'MI3110025', 'nama' => 'Pemrograman Web', 'sks' => 3, 'ket' => 'Praktek', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 3, 'kode' => 'MI3110026', 'nama' => 'Analisis dan Desain Sistem', 'sks' => 3, 'ket' => 'Praktek', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 3, 'kode' => 'MI3110027', 'nama' => 'Aplikasi Perkantoran II', 'sks' => 3, 'ket' => 'Praktek', 'sifat' => 'W', 'prasyarat' => ['MI1110007']], // SMT 1/7
            ['sem' => 3, 'kode' => 'MI3110028', 'nama' => 'Pemrograman Berorientasi Objek', 'sks' => 3, 'ket' => 'Praktek', 'sifat' => 'W', 'prasyarat' => ['MI1110010']], // SMT 1/10
            ['sem' => 3, 'kode' => 'MI3110029', 'nama' => 'Expert and Decission System', 'sks' => 2, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => []],

            // SEMESTER 4
            ['sem' => 4, 'kode' => 'MI4110030', 'nama' => 'Network Security', 'sks' => 3, 'ket' => 'Praktek', 'sifat' => 'W', 'prasyarat' => ['MI3110024']], // SMT III/4
            ['sem' => 4, 'kode' => 'MI4110031', 'nama' => 'Rekayasa Perangkat Lunak', 'sks' => 3, 'ket' => 'Praktek', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 4, 'kode' => 'MI4110032', 'nama' => 'Sistem Informasi Geografis', 'sks' => 3, 'ket' => 'Praktek', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 4, 'kode' => 'MI4110033', 'nama' => 'Audit Sistem Informasi', 'sks' => 3, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => []], // Ditulis 'SMT III' namun tidak spesifik MK-nya
            ['sem' => 4, 'kode' => 'MI4110034', 'nama' => 'Sistem Informasi Akuntansi', 'sks' => 3, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => ['MI2110016']], // SMT II/6
            ['sem' => 4, 'kode' => 'MI4110035', 'nama' => 'Sistem Informasi Manajemen', 'sks' => 3, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => ['MI2110016']], // SMT II/6
            ['sem' => 4, 'kode' => 'MI4110036', 'nama' => 'Etika Profesi', 'sks' => 2, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 4, 'kode' => 'MI4110037', 'nama' => 'Manajemen Proyek', 'sks' => 2, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => []],
            ['sem' => 4, 'kode' => 'MI4110038', 'nama' => 'Teknik Penulisan Ilmiah', 'sks' => 2, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => []],

            // SEMESTER 5
            ['sem' => 5, 'kode' => 'MI5110039', 'nama' => 'PKL', 'sks' => 20, 'ket' => 'Praktek', 'sifat' => 'P', 'prasyarat' => []],

            // SEMESTER 6
            ['sem' => 6, 'kode' => 'MI6110040', 'nama' => 'Tugas Akhir', 'sks' => 6, 'ket' => 'Teori', 'sifat' => 'W', 'prasyarat' => ['MI4110038', 'MI5110039']], // SMT IV/9 & V
        ];

        $kurikulumMkIds = []; // Dictionary untuk menyimpan ID kurikulum_mk (Berguna untuk Prasyarat)

        // 4. Lakukan Insert/Update ke Master Mata Kuliah & Kurikulum Mata Kuliah
        foreach ($courses as $course) {
            $isPraktek = ($course['ket'] === 'Praktek');
            $activityType = ($course['nama'] === 'Tugas Akhir') ? 'THESIS' : 'REGULAR';

            // Insert/Update Master Mata Kuliah
            DB::table('master_mata_kuliahs')->updateOrInsert(
                [
                    'prodi_id' => $prodi->id,
                    'kode_mk'  => $course['kode'],
                ],
                [
                    'nama_mk'        => $course['nama'],
                    'sks_default'    => $course['sks'],
                    'sks_tatap_muka' => $isPraktek ? 0 : $course['sks'],
                    'sks_praktek'    => $isPraktek ? $course['sks'] : 0,
                    'sks_lapangan'   => 0,
                    'jenis_mk'       => 'A',
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
                    'sks_tatap_muka' => $isPraktek ? 0 : $course['sks'],
                    'sks_praktek'    => $isPraktek ? $course['sks'] : 0,
                    'sks_lapangan'   => 0,
                    'sifat_mk'       => $course['sifat'],
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]
            );

            // Fetch Kurikulum MK ID dan simpan ke dalam array dictionary
            $kurikulumMk = DB::table('kurikulum_mata_kuliah')
                ->where('kurikulum_id', $kurikulum->id)
                ->where('mata_kuliah_id', $mk->id)
                ->first();

            $kurikulumMkIds[$course['kode']] = $kurikulumMk->id;
        }

        // 5. Lakukan Pemetaan Prasyarat Berdasarkan Dictionary
        foreach ($courses as $course) {
            if (!empty($course['prasyarat'])) {
                $targetKurikulumMkId = $kurikulumMkIds[$course['kode']];

                foreach ($course['prasyarat'] as $kodePrasyarat) {
                    // Pastikan kode prasyarat ada di dictionary
                    if (isset($kurikulumMkIds[$kodePrasyarat])) {
                        $prasyaratId = $kurikulumMkIds[$kodePrasyarat];

                        DB::table('kurikulum_mk_prasyarat')->updateOrInsert(
                            [
                                'kurikulum_mk_id'           => $targetKurikulumMkId,
                                'prasyarat_kurikulum_mk_id' => $prasyaratId,
                            ],
                            [
                                'min_nilai_huruf' => 'C', // Karena di footer ditulis "Lulus dengan nilai mata kuliah minimal C"
                                'min_nilai'       => 2.00,
                                'logic_type'      => 'AND',
                            ]
                        );
                    }
                }
            }
        }

        $this->command->info('Seeding Kurikulum, Mata Kuliah, dan Prasyarat berhasil diselesaikan!');
    }
}
