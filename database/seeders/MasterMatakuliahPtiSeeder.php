<?php

namespace Database\Seeders;

use App\Models\MasterMataKuliah;
use App\Models\RefProdi;
use Illuminate\Database\Seeder;

class MasterMatakuliahPtiSeeder extends Seeder
{
    /**
     * Seeder Master Mata Kuliah S1 Pendidikan Teknologi Informasi (PTI)
     * Berdasarkan Kurikulum 2024 UNMARIS (Update sesuai Excel)
     */
    public function run(): void
    {
        $this->command->info('Memulai seeding Master Mata Kuliah PTI sesuai Kurikulum 2024 (Data Real Excel)...');

        // 1. Ambil Prodi PTI
        $prodi = RefProdi::where('kode_prodi_internal', 'PTI')->first();

        if (!$prodi) {
            $this->command->error("Prodi dengan kode 'PTI' tidak ditemukan.");
            return;
        }

        // 2. Daftar Mata Kuliah
        // tm = Teori, p = Praktikum, l = Lapangan
        // jenis: A=Wajib Nasional, B=Wajib Prodi, C=Pilihan, D=TA/Lainnya
        $courses = [
            // --- SEMESTER 1 ---
            ['kode' => 'PTI24-4101', 'nama' => 'Pendidikan Agama', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'A'],
            ['kode' => 'PTI24-4102', 'nama' => 'Landasan Pendidikan', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4103', 'nama' => 'Pancasila', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'A'],
            ['kode' => 'PTI24-4104', 'nama' => 'Matematika Dasar', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4105', 'nama' => 'Bahasa Inggris', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'A'],
            ['kode' => 'PTI24-4106', 'nama' => 'Pengantar Tik', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4107', 'nama' => 'Filsafat Pendidikan', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4108', 'nama' => 'Bahasa Indonesia', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'A'],
            ['kode' => 'PTI24-4109', 'nama' => 'Psikologi Pendidikan', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4110', 'nama' => 'Aplikasi Komputer', 'tm' => 0, 'p' => 2, 'l' => 0, 'jenis' => 'B'],

            // --- SEMESTER 2 ---
            ['kode' => 'PTI24-4211', 'nama' => 'Algoritma Pemrograman Dasar', 'tm' => 0, 'p' => 2, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4212', 'nama' => 'Kurikulum Sekolah', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4213', 'nama' => 'Pendidikan Anti Korupsi', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'A'],
            ['kode' => 'PTI24-4214', 'nama' => 'Organisasi Arsitektur Komputer', 'tm' => 0, 'p' => 2, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4215', 'nama' => 'Sumber Belajar dan Media Pembelajaran', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4216', 'nama' => 'Kalkulus I', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4217', 'nama' => 'Multimedia', 'tm' => 0, 'p' => 2, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4218', 'nama' => 'Pengelolaan Pendidikan', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4219', 'nama' => 'Kewirausahaan', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'A'],
            ['kode' => 'PTI24-4220', 'nama' => 'Profesi Kependidikan', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],

            // --- SEMESTER 3 ---
            ['kode' => 'PTI24-4321', 'nama' => 'Struktur Data', 'tm' => 0, 'p' => 3, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4322', 'nama' => 'Algoritma Pemrograman Lanjut', 'tm' => 0, 'p' => 3, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4323', 'nama' => 'Bimbingan dan Konseling', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4324', 'nama' => 'Pedagogik', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4325', 'nama' => 'Logika Informatika', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4326', 'nama' => 'Sistem Operasi', 'tm' => 0, 'p' => 3, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4327', 'nama' => 'Sistem Basis Data', 'tm' => 0, 'p' => 3, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4328', 'nama' => 'dasar-dasar Statistik', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4329', 'nama' => 'Bahasa Inggris Lanjutan', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'A'],

            // --- SEMESTER 4 ---
            ['kode' => 'PTI24-4430', 'nama' => 'Desain Web', 'tm' => 0, 'p' => 3, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4431', 'nama' => 'Jaringan Komputer Dasar', 'tm' => 0, 'p' => 3, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4432', 'nama' => 'E-Learning', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4433', 'nama' => 'Pemrograman Web Kependidikan', 'tm' => 0, 'p' => 3, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4434', 'nama' => 'Inovasi Kurikulum', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4435', 'nama' => 'Kecerdasan Buatan dalam Pendidikan', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4436', 'nama' => 'Metodologi Penelitian', 'tm' => 2, 'p' => 1, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4437', 'nama' => 'Wawasan Budaya lokal Sumba', 'tm' => 1, 'p' => 2, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4438', 'nama' => 'Game Edukasi', 'tm' => 1, 'p' => 2, 'l' => 0, 'jenis' => 'C'],

            // --- SEMESTER 5 ---
            ['kode' => 'PTI24-4539', 'nama' => 'Jaringan Komputer Lanjut', 'tm' => 0, 'p' => 3, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4540', 'nama' => 'Pemrograman Berorientasi Objek', 'tm' => 0, 'p' => 3, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4541', 'nama' => 'strategi Pembelajaran', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4542', 'nama' => 'Teknologi Pembelajaran', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4543', 'nama' => 'Evaluasi Pembelajaran', 'tm' => 3, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4544', 'nama' => 'Leadership', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4545', 'nama' => 'Publik Speaking', 'tm' => 0, 'p' => 3, 'l' => 0, 'jenis' => 'C'],

            // --- SEMESTER 6 ---
            ['kode' => 'PTI24-4647', 'nama' => 'UI/UX Pembelajaran Digital', 'tm' => 1, 'p' => 2, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4648', 'nama' => 'Microteaching', 'tm' => 0, 'p' => 3, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4649', 'nama' => 'Pendidikan Tindakan Kelas', 'tm' => 1, 'p' => 2, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4650', 'nama' => 'Rekayasa Perangkat Lunak', 'tm' => 0, 'p' => 3, 'l' => 0, 'jenis' => 'B'],
            ['kode' => 'PTI24-4651', 'nama' => 'Interaksi Komputer dan Manusia', 'tm' => 0, 'p' => 3, 'l' => 0, 'jenis' => 'C'],

            // --- SEMESTER 7 ---
            ['kode' => 'PTI24-4751', 'nama' => 'Magang/PPL', 'tm' => 0, 'p' => 20, 'l' => 0, 'jenis' => 'D'],

            // --- SEMESTER 8 ---
            ['kode' => 'PTI24-4852', 'nama' => 'Proposal skripsi', 'tm' => 2, 'p' => 0, 'l' => 0, 'jenis' => 'D', 'type' => 'INTERNSHIP'],
            ['kode' => 'PTI24-4853', 'nama' => 'Tugas Akhir', 'tm' => 2, 'p' => 2, 'l' => 0, 'jenis' => 'D', 'type' => 'THESIS'],
        ];

        // 3. Eksekusi Penyimpanan
        foreach ($courses as $c) {
            $totalSks = $c['tm'] + $c['p'] + $c['l'];

            // Tentukan activity_type: Default REGULAR, kecuali ditentukan lain
            $activityType = $c['type'] ?? 'REGULAR';

            MasterMataKuliah::updateOrInsert(
                [
                    'prodi_id' => $prodi->id,
                    'kode_mk' => $c['kode']
                ],
                [
                    'nama_mk' => $c['nama'],
                    'sks_default' => $totalSks,
                    'sks_tatap_muka' => $c['tm'],
                    'sks_praktek' => $c['p'],
                    'sks_lapangan' => $c['l'],
                    'jenis_mk' => $c['jenis'],
                    'activity_type' => $activityType,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $this->command->info("Berhasil menambahkan " . count($courses) . " Master Mata Kuliah PTI sesuai standar Excel.");
    }
}
