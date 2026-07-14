<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterKurikulum as Kurikulum;
use App\Models\masterMataKuliah as Matakuliah;
use App\Models\RefProdi as Prodi;
use App\Models\KurikulumMataKuliah;

class KurikulumPTISeeder extends Seeder
{
    /**
     * Seeder Pemetaan Kurikulum PTI 2024
     * SKS diambil LANGSUNG dari draf Excel, bukan dari Master MK.
     */
    public function run(): void
    {
        $this->command->info('Memproses Pemetaan Kurikulum PTI 2024 (SKS dari Excel)...');

        // 1. Ambil Prodi Pendidikan Teknologi Informasi
        $prodi = Prodi::where('kode_prodi_internal', 'PTI')->first();

        if (!$prodi) {
            $this->command->error("Prodi PTI tidak ditemukan. Pastikan data Prodi sudah ada.");
            return;
        }

        // 2. Buat/Update Header Kurikulum
        $kurikulum = Kurikulum::updateOrCreate(
            [
                'prodi_id' => $prodi->id,
                'nama_kurikulum' => 'Kurikulum PTI 2024'
            ],
            [
                'tahun_mulai' => 2024,
                'id_semester_mulai' => '20241',
                'jumlah_sks_lulus' => 144,
                'is_active' => true,
            ]
        );

        // 3. Definisi Struktur Mata Kuliah (SKS, Teori, dan Praktik sesuai Excel)
        $mappings = [
            // --- SEMESTER 1 ---
            ['kode' => 'PTI24-4101', 'smt' => 1, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // PENDIDIKAN AGAMA
            ['kode' => 'PTI24-4102', 'smt' => 1, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // LANDASAN PENDIDIKAN
            ['kode' => 'PTI24-4103', 'smt' => 1, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // PANCASILA
            ['kode' => 'PTI24-4104', 'smt' => 1, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // MATEMATIKA DASAR
            ['kode' => 'PTI24-4105', 'smt' => 1, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // BAHASA INGGRIS
            ['kode' => 'PTI24-4106', 'smt' => 1, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // PENGANTAR TIK
            ['kode' => 'PTI24-4107', 'smt' => 1, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // FILSAFAT PENDIDIKAN
            ['kode' => 'PTI24-4108', 'smt' => 1, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // BAHASA INDONESIA
            ['kode' => 'PTI24-4109', 'smt' => 1, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // PSIKOLOGI PENDIDIKAN
            ['kode' => 'PTI24-4110', 'smt' => 1, 'sifat' => 'W', 'sks' => 2, 't' => 0, 'p' => 2], // APLIKASI KOMPUTER

            // --- SEMESTER 2 ---
            ['kode' => 'PTI24-4211', 'smt' => 2, 'sifat' => 'W', 'sks' => 2, 't' => 0, 'p' => 2], // Algoritma Pemrograman Dasar
            ['kode' => 'PTI24-4212', 'smt' => 2, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // Kurikulum Sekolah
            ['kode' => 'PTI24-4213', 'smt' => 2, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // Pendidikan Anti Korupsi
            ['kode' => 'PTI24-4214', 'smt' => 2, 'sifat' => 'W', 'sks' => 2, 't' => 0, 'p' => 2], // Organisasi Arsitektur Komputer
            ['kode' => 'PTI24-4215', 'smt' => 2, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // Sumber Belajar dan Media Pembelajaran
            ['kode' => 'PTI24-4216', 'smt' => 2, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // Kalkulus I
            ['kode' => 'PTI24-4217', 'smt' => 2, 'sifat' => 'W', 'sks' => 2, 't' => 0, 'p' => 2], // Multimedia
            ['kode' => 'PTI24-4218', 'smt' => 2, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // Pengelolaan Pendidikan
            ['kode' => 'PTI24-4219', 'smt' => 2, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // Kewirausahaan
            ['kode' => 'PTI24-4220', 'smt' => 2, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // Profesi Kependidikan

            // --- SEMESTER 3 ---
            ['kode' => 'PTI24-4321', 'smt' => 3, 'sifat' => 'W', 'sks' => 3, 't' => 0, 'p' => 3], // Struktur Data
            ['kode' => 'PTI24-4322', 'smt' => 3, 'sifat' => 'W', 'sks' => 3, 't' => 0, 'p' => 3], // Algoritma Pemrograman Lanjut
            ['kode' => 'PTI24-4323', 'smt' => 3, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // Bimbingan dan Konseling
            ['kode' => 'PTI24-4324', 'smt' => 3, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // Pedagogik
            ['kode' => 'PTI24-4325', 'smt' => 3, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // Logika Informatika
            ['kode' => 'PTI24-4326', 'smt' => 3, 'sifat' => 'W', 'sks' => 3, 't' => 0, 'p' => 3], // Sistem Operasi
            ['kode' => 'PTI24-4327', 'smt' => 3, 'sifat' => 'W', 'sks' => 3, 't' => 0, 'p' => 3], // Sistem Basis Data
            ['kode' => 'PTI24-4328', 'smt' => 3, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // dasar-dasar Statistik
            ['kode' => 'PTI24-4329', 'smt' => 3, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // Bahasa Inggris Lanjutan

            // --- SEMESTER 4 ---
            ['kode' => 'PTI24-4430', 'smt' => 4, 'sifat' => 'W', 'sks' => 3, 't' => 0, 'p' => 3], // Desain Web
            ['kode' => 'PTI24-4431', 'smt' => 4, 'sifat' => 'W', 'sks' => 3, 't' => 0, 'p' => 3], // Jaringan Komputer Dasar
            ['kode' => 'PTI24-4432', 'smt' => 4, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // E-Learning
            ['kode' => 'PTI24-4433', 'smt' => 4, 'sifat' => 'W', 'sks' => 3, 't' => 0, 'p' => 3], // Pemrograman Web Kependidikan
            ['kode' => 'PTI24-4434', 'smt' => 4, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // Inovasi Kurikulum
            ['kode' => 'PTI24-4435', 'smt' => 4, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // Kecerdasan Buatan dalam Pendidikan
            ['kode' => 'PTI24-4436', 'smt' => 4, 'sifat' => 'W', 'sks' => 3, 't' => 3, 'p' => 0], // Metodologi Penelitian
            ['kode' => 'PTI24-4437', 'smt' => 4, 'sifat' => 'W', 'sks' => 3, 't' => 0, 'p' => 3], // Wawasan Budaya lokal Sumba
            ['kode' => 'PTI24-4438', 'smt' => 4, 'sifat' => 'P', 'sks' => 3, 't' => 0, 'p' => 3], // Pilihan : Game Edukasi

            // --- SEMESTER 5 ---
            ['kode' => 'PTI24-4539', 'smt' => 5, 'sifat' => 'W', 'sks' => 3, 't' => 0, 'p' => 3], // Jaringan Komputer Lanjut
            ['kode' => 'PTI24-4540', 'smt' => 5, 'sifat' => 'W', 'sks' => 3, 't' => 0, 'p' => 3], // Pemrograman Berorientasi Objek
            ['kode' => 'PTI24-4541', 'smt' => 5, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // strategi Pembelajaran
            ['kode' => 'PTI24-4542', 'smt' => 5, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // Teknologi Pembelajaran
            ['kode' => 'PTI24-4543', 'smt' => 5, 'sifat' => 'W', 'sks' => 3, 't' => 3, 'p' => 0], // Evaluasi Pembelajaran
            ['kode' => 'PTI24-4544', 'smt' => 5, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // Leadership
            ['kode' => 'PTI24-4545', 'smt' => 5, 'sifat' => 'P', 'sks' => 3, 't' => 0, 'p' => 3], // Pilihan: Publik Speaking

            // --- SEMESTER 6 ---
            ['kode' => 'PTI24-4647', 'smt' => 6, 'sifat' => 'W', 'sks' => 3, 't' => 0, 'p' => 3], // UI/UX Pembelajaran Digital
            ['kode' => 'PTI24-4648', 'smt' => 6, 'sifat' => 'W', 'sks' => 3, 't' => 0, 'p' => 3], // Microteaching
            ['kode' => 'PTI24-4649', 'smt' => 6, 'sifat' => 'W', 'sks' => 3, 't' => 0, 'p' => 3], // Pendidikan Tindakan Kelas
            ['kode' => 'PTI24-4650', 'smt' => 6, 'sifat' => 'P', 'sks' => 3, 't' => 0, 'p' => 3], // Rekayasa Perangkat Lunak
            ['kode' => 'PTI24-4751', 'smt' => 7, 'sifat' => 'W', 'sks' => 20, 't' => 0, 'p' => 20], // Interaksi Komputer dan Manusia

            // --- SEMESTER 7 ---
            ['kode' => 'PTI24-4751', 'smt' => 7, 'sifat' => 'W', 'sks' => 20, 't' => 0, 'p' => 20], // Magang/PPL

            // --- SEMESTER 8 ---
            ['kode' => 'PTI24-4852', 'smt' => 8, 'sifat' => 'W', 'sks' => 2, 't' => 2, 'p' => 0], // Proposal skripsi
            ['kode' => 'PTI24-4853', 'smt' => 8, 'sifat' => 'W', 'sks' => 4, 't' => 2, 'p' => 2], // Skripsi
        ];

        // 4. Proses Sinkronisasi
        $totalWajib = 0;
        $totalPilihan = 0;

        foreach ($mappings as $map) {
            $mk = MataKuliah::where('kode_mk', $map['kode'])->where('prodi_id', $prodi->id)->first();

            if (!$mk) {
                $this->command->warn("Mata kuliah {$map['kode']} ({$map['smt']}) tidak ditemukan di tabel mata_kuliah.");
                continue;
            }

            // Simpan ke Tabel Kurikulum Mata Kuliah LANGSUNG menggunakan SKS dari Excel
            KurikulumMataKuliah::updateOrCreate(
                [
                    'kurikulum_id' => $kurikulum->id,
                    'mata_kuliah_id' => $mk->id
                ],
                [
                    'semester_paket' => $map['smt'],
                    'sks_tatap_muka' => $map['t'], // Diambil dari mapping Excel (t)
                    'sks_praktek'    => $map['p'], // Diambil dari mapping Excel (p)
                    'sks_lapangan'   => 0,         // Default 0 kecuali ada di excel
                    'sifat_mk'       => $map['sifat'],
                ]
            );

            // Akumulasi SKS LANGSUNG dari mapping (bukan dari $mk->sks_default)
            $sks = $map['sks'];
            if ($map['sifat'] == 'W') {
                $totalWajib += $sks;
            } else {
                $totalPilihan += $sks;
            }
        }

        // 5. Update Header Total SKS
        $kurikulum->update([
            'jumlah_sks_wajib' => $totalWajib,
            'jumlah_sks_pilihan' => $totalPilihan
        ]);

        $this->command->info("Penyusunan Kurikulum PTI Selesai. Total tersinkron: {$totalWajib} SKS Wajib & {$totalPilihan} SKS Pilihan.");
    }
}
