<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterMataKuliah as MataKuliah;
use App\Models\RefProdi as Prodi;

class MasterMatakuliahArsSeeder extends Seeder
{
    /**
     * Seeder untuk Master Mata Kuliah S1 Administrasi Rumah Sakit (ARS)
     * Disesuaikan dengan Kurikulum KPT 4.0
     */
    public function run(): void
    {
        $this->command->info('Memulai seeding Master Mata Kuliah ARS...');

        // 1. Ambil Prodi ARS
        $prodi = Prodi::where('kode_prodi_internal', 'ARS')->first();

        if (!$prodi) {
            $this->command->error("Prodi dengan kode 'ARS' tidak ditemukan.");
            return;
        }

        // 2. Daftar Mata Kuliah
        // A = Wajib Nasional
        // B = Wajib Prodi (Keahlian)
        // C = Pilihan
        // D = Tugas Akhir/Skripsi/Magang

        $courses = [
            // --- SEMESTER 1 ---
            ['kode' => 'ARS-113261001', 'nama' => 'Pendidikan Agama', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'A'],
            ['kode' => 'ARS-113261002', 'nama' => 'Pendidikan Pancasila', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'A'],
            ['kode' => 'ARS-113261003', 'nama' => 'Bahasa Inggris', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'A'],
            ['kode' => 'ARS-113261004', 'nama' => 'Dasar-dasar Administrasi dan Manajemen', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-113261005', 'nama' => 'Ilmu Kesehatan Masyarakat', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-113261006', 'nama' => 'Antropologi Sosial Dan Kesehatan', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-113261007', 'nama' => 'Pendidikan Anti Korupsi', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'A'],
            ['kode' => 'ARS-113261008', 'nama' => 'Dasar-Dasar Komunikasi', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-113261009', 'nama' => 'Psikologi Kesehatan', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-113261010', 'nama' => 'Tata Kelola Dan Kepemimpinan', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],

            // --- SEMESTER 2 ---
            ['kode' => 'ARS-213261001', 'nama' => 'Pendidikan Kewarganegaraan', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'A'],
            ['kode' => 'ARS-213261002', 'nama' => 'Bahasa Indonesia', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'A'],
            ['kode' => 'ARS-213261003', 'nama' => 'Administrasi Rumah Sakit', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-213261004', 'nama' => 'Dasar-Dasar Statistik', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-213261005', 'nama' => 'Dasar-Dasar Akuntansi', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-213261006', 'nama' => 'Etika Profesi Dan Hukum Kesehatan', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-213261007', 'nama' => 'Terminologi Medis', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-213261008', 'nama' => 'Perilaku Organisasi', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-2113261009', 'nama' => 'Kewirausahaan (Entrepreunership)', 'sks' => 2, 'tm' => 1, 'p' => 1, 'l' => 0, 'jenis' => 'A'],
            ['kode' => 'ARS-2113261010', 'nama' => 'Penyuluhan Kesehatan Rumah Sakit (PKRS)', 'sks' => 2, 'tm' => 1, 'p' => 1, 'l' => 0, 'jenis' => 'B'],

            // --- SEMESTER 3 ---
            ['kode' => 'ARS-3113261001', 'nama' => 'Biostatistik dan Statistik Kesehatan', 'sks' => 3, 'tm' => 2, 'p' => 1, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-3113261002', 'nama' => 'Akuntansi Biaya (Implementasi di RS)', 'sks' => 3, 'tm' => 2, 'p' => 1, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-3113261003', 'nama' => 'Aplikasi Komputer', 'sks' => 3, 'tm' => 2, 'p' => 1, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-313261004', 'nama' => 'Manajemen Rekam Medis', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-3113261005', 'nama' => 'Manajemen Keselamatan dan Kesehatan Kerja', 'sks' => 3, 'tm' => 2, 'p' => 1, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-313261006', 'nama' => 'Epidemiologi Klinik', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-3113261007', 'nama' => 'Bahasa Inggris Profesi', 'sks' => 2, 'tm' => 1, 'p' => 1, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-313261008', 'nama' => 'Manajemen Pelayanan Kesehatan (UKM & UKP)', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],

            // --- SEMESTER 4 ---
            ['kode' => 'ARS-4113261001', 'nama' => 'Manajemen Pelayanan Kesehatan di RS', 'sks' => 3, 'tm' => 2, 'p' => 1, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-4113261002', 'nama' => 'Manajemen Logistik Dan Non Medis', 'sks' => 3, 'tm' => 2, 'p' => 1, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-4113261003', 'nama' => 'Sistem Informasi Manajemen RS', 'sks' => 3, 'tm' => 2, 'p' => 1, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-4113261004', 'nama' => 'Manajemen Keuangan RS', 'sks' => 3, 'tm' => 2, 'p' => 1, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-413261005', 'nama' => 'Manajemen Sumber Daya Manusia Kesehatan', 'sks' => 3, 'tm' => 3, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-4113261006', 'nama' => 'Manajemen Kesehatan Lingkungan RS', 'sks' => 2, 'tm' => 1, 'p' => 1, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-413261007', 'nama' => 'Administrasi Kebijakan Rumah Sakit (AKRS)', 'sks' => 3, 'tm' => 3, 'p' => 0, 'l' => 0, 'jenis' => 'B'],

            // --- SEMESTER 5 ---
            ['kode' => 'ARS-5113261001', 'nama' => 'Manajemen Jaminan Mutu Pelayanan Kesehatan', 'sks' => 3, 'tm' => 2, 'p' => 1, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-513261002', 'nama' => 'Tata Kelola Rumah Sakit', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-5113261003', 'nama' => 'Manajemen Produksi (implementasi di RS)', 'sks' => 3, 'tm' => 2, 'p' => 1, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-5113261004', 'nama' => 'Anggaran dan Indikator kinerja', 'sks' => 2, 'tm' => 1, 'p' => 1, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-5113261005', 'nama' => 'Manajemen Risiko RS', 'sks' => 3, 'tm' => 2, 'p' => 1, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-513261006', 'nama' => 'Manajemen Pemasaran RS', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-5113261007', 'nama' => 'Evaluasi Kinerja RS', 'sks' => 2, 'tm' => 1, 'p' => 1, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-5113261008', 'nama' => 'Sistem Perencanaan RS', 'sks' => 3, 'tm' => 2, 'p' => 1, 'l' => 0, 'jenis' => 'B'],

            // --- SEMESTER 6 ---
            ['kode' => 'ARS-6113261001', 'nama' => 'Manajemen Komplain dan Costumer Service', 'sks' => 2, 'tm' => 1, 'p' => 1, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-613261002', 'nama' => 'Manajemen Akreditasi Rumah Sakit', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-6113261003', 'nama' => 'Manajemen Aset dan Tata Ruang', 'sks' => 2, 'tm' => 1, 'p' => 1, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-613261004', 'nama' => 'Hospital Public Relation', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-6113261005', 'nama' => 'Manajemen Bisnis (RSB dan RBA)', 'sks' => 2, 'tm' => 1, 'p' => 1, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-613261006', 'nama' => 'Manajemen Penunjang Medis', 'sks' => 2, 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-6113261007', 'nama' => 'Studi Kelayakan Proyek', 'sks' => 2, 'tm' => 1, 'p' => 1, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-6113261008', 'nama' => 'Metodologi Penelitian', 'sks' => 3, 'tm' => 2, 'p' => 1, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'ARS-6113261009', 'nama' => 'Pengalaman Belajar Lapangan (PBL)', 'sks' => 3, 'tm' => 0, 'p' => 0, 'l' => 3, 'jenis' => 'D'],

            // --- SEMESTER 7 ---
            ['kode' => 'ARS-7113261008', 'nama' => 'Magang', 'sks' => 20, 'tm' => 0, 'p' => 0, 'l' => 20, 'jenis' => 'D', 'activity_type' => 'INTERNSHIP'],

            // --- SEMESTER 8 ---
            ['kode' => 'ARS-8113261002', 'nama' => 'Tugas Akhir', 'sks' => 4, 'tm' => 0, 'p' => 0, 'l' => 4, 'jenis' => 'D', 'activity_type' => 'THESIS'],
        ];

        // 3. Eksekusi Penyimpanan
        $count = 0;
        foreach ($courses as $c) {
            $activityType = 'REGULAR';
            if ($c['kode'] === 'K3-81002') $activityType = 'THESIS';
            if ($c['kode'] === 'K3-71001') $activityType = 'INTERNSHIP';
            MataKuliah::updateOrCreate(
                [
                    'prodi_id' => $prodi->id,
                    'kode_mk' => $c['kode']
                ],
                [
                    'nama_mk' => $c['nama'],
                    'sks_default' => $c['sks'],
                    'sks_tatap_muka' => $c['tm'],
                    'sks_praktek' => $c['p'],
                    'sks_lapangan' => $c['l'],
                    'jenis_mk' => $c['jenis'],
                    'activity_type' => $c['activity_type'] ?? "REGULAR",
                    'updated_at' => now(),
                ]
            );
            $count++;
        }

        $this->command->info("Berhasil menambahkan {$count} Master Mata Kuliah beserta activity_type untuk Administrasi Rumah Sakit.");
    }
}
