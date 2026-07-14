<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class MasterMatakuliahTISeeder extends Seeder
{
    public function run(): void
    {
        $prodi = DB::table('ref_prodi')->where('kode_prodi_internal', 'TI')->first();
        if (!$prodi) {
            $this->command->error("Prodi TI tidak ditemukan!");
            return;
        }

        $now = Carbon::now();

        // Data LENGKAP Semester 1 - 8
        $data = [
            // SEMESTER 1
            ['kode' => 'TI1120001', 'nama' => 'Pendidikan Agama', 'tm' => 2, 'p' => 0, 'l' => 0, 'jml' => 2],
            ['kode' => 'TI1120002', 'nama' => 'Bahasa Indonesia', 'tm' => 2, 'p' => 0, 'l' => 0, 'jml' => 2],
            ['kode' => 'TI1120003', 'nama' => 'Pengantar Teknologi Informasi', 'tm' => 2, 'p' => 0, 'l' => 0, 'jml' => 2],
            ['kode' => 'TI1120004', 'nama' => 'Kalkulus', 'tm' => 3, 'p' => 0, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI1120005', 'nama' => 'Dasar-dasar Pemrograman (Bahasa C)', 'tm' => 3, 'p' => 0, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI1120006', 'nama' => 'Aplikasi Perkantoran I', 'tm' => 0, 'p' => 3, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI1120007', 'nama' => 'Sistem Operasi', 'tm' => 3, 'p' => 0, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI1120008', 'nama' => 'Bahasa Inggris I', 'tm' => 2, 'p' => 0, 'l' => 0, 'jml' => 2],

            // SEMESTER 2
            ['kode' => 'TI2120009', 'nama' => 'Pendidikan Pancasila', 'tm' => 2, 'p' => 0, 'l' => 0, 'jml' => 2],
            ['kode' => 'TI2120010', 'nama' => 'Aljabar Linear dan Matriks', 'tm' => 2, 'p' => 0, 'l' => 0, 'jml' => 2],
            ['kode' => 'TI2120011', 'nama' => 'Pemrograman Berorientasi Objek (Java)', 'tm' => 0, 'p' => 3, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI2120012', 'nama' => 'Arsitektur dan Organisasi Komputer', 'tm' => 0, 'p' => 3, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI2120013', 'nama' => 'Konsep Jaringan Komputer', 'tm' => 3, 'p' => 0, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI2120014', 'nama' => 'Algoritma dan Struktur Data', 'tm' => 3, 'p' => 0, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI2120015', 'nama' => 'Sistem Basis Data', 'tm' => 0, 'p' => 3, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI2120016', 'nama' => 'Bahasa Inggris Pemrograman', 'tm' => 2, 'p' => 0, 'l' => 0, 'jml' => 2],

            // SEMESTER 3
            ['kode' => 'TI3120017', 'nama' => 'Pendidikan Anti Korupsi', 'tm' => 2, 'p' => 0, 'l' => 0, 'jml' => 2],
            ['kode' => 'TI3120018', 'nama' => 'Statistika dan Probabilitas', 'tm' => 3, 'p' => 0, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI3120019', 'nama' => 'Etika Hukum Cyber', 'tm' => 2, 'p' => 0, 'l' => 0, 'jml' => 2],
            ['kode' => 'TI3120020', 'nama' => 'Interaksi Manusia-Komputer', 'tm' => 3, 'p' => 0, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI3120021', 'nama' => 'Aplikasi Perkantoran II', 'tm' => 0, 'p' => 3, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI3120022', 'nama' => 'Pemrograman Lanjutan (C++)', 'tm' => 0, 'p' => 3, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI3120023', 'nama' => 'Sistem Jaringan Komputer', 'tm' => 0, 'p' => 3, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI3120024', 'nama' => 'Matematika Diskrit dan Logika', 'tm' => 2, 'p' => 0, 'l' => 0, 'jml' => 2],

            // SEMESTER 4
            ['kode' => 'TI4120025', 'nama' => 'Broadcasting', 'tm' => 3, 'p' => 0, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI4120026', 'nama' => 'Rekayasa Perangkat Lunak', 'tm' => 3, 'p' => 0, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI4120027', 'nama' => 'Administrasi Jaringan', 'tm' => 0, 'p' => 3, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI4120028', 'nama' => 'Pengembangan Aplikasi Web', 'tm' => 0, 'p' => 3, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI4120029', 'nama' => 'Metode Numerik', 'tm' => 3, 'p' => 0, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI4120030', 'nama' => 'Grafika Komputer (C#)', 'tm' => 0, 'p' => 3, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI4120031', 'nama' => 'Sistem Berkas', 'tm' => 2, 'p' => 0, 'l' => 0, 'jml' => 2],

            // SEMESTER 5
            ['kode' => 'TI5120033', 'nama' => 'Network Security', 'tm' => 2, 'p' => 0, 'l' => 0, 'jml' => 2],
            ['kode' => 'TI5120034', 'nama' => 'Pengembangan Aplikasi Mobile', 'tm' => 0, 'p' => 3, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI5120035', 'nama' => 'Sistem Informasi Geografis', 'tm' => 0, 'p' => 3, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI5120036', 'nama' => 'Sistem dan Aplikasi Multimedia', 'tm' => 0, 'p' => 3, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI5120037', 'nama' => 'Expert and Decission System', 'tm' => 0, 'p' => 3, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI5120038', 'nama' => 'Project E-business', 'tm' => 0, 'p' => 2, 'l' => 0, 'jml' => 2],
            ['kode' => 'TI5120039', 'nama' => 'Etika Profesi', 'tm' => 2, 'p' => 0, 'l' => 0, 'jml' => 2],

            // SEMESTER 6
            ['kode' => 'TI6120040', 'nama' => 'Leadership', 'tm' => 2, 'p' => 0, 'l' => 0, 'jml' => 2],
            ['kode' => 'TI6120041', 'nama' => 'Metodologi Penulisan Ilmiah', 'tm' => 2, 'p' => 0, 'l' => 0, 'jml' => 2],
            ['kode' => 'TI6120042', 'nama' => 'Pengembangan Web Lanjutan', 'tm' => 0, 'p' => 3, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI6120043', 'nama' => 'Teknologi Kecerdasan Bisnis', 'tm' => 0, 'p' => 3, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI6120044', 'nama' => 'Pemrograman Visual (VB Net)', 'tm' => 0, 'p' => 3, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI6120045', 'nama' => 'Pemodelan dan Pengujian Sistem', 'tm' => 0, 'p' => 3, 'l' => 0, 'jml' => 3],
            ['kode' => 'TI6120046', 'nama' => 'Sistem dan Aplikasi Multimedia II', 'tm' => 0, 'p' => 3, 'l' => 0, 'jml' => 3],

            // SEMESTER 7 & 8
            ['kode' => 'TI7120047', 'nama' => 'Magang', 'tm' => 0, 'p' => 0, 'l' => 20, 'jml' => 20],
            ['kode' => 'TI8120048', 'nama' => 'Tugas Akhir', 'tm' => 0, 'p' => 0, 'l' => 8, 'jml' => 8],
        ];

        $this->command->info('Membersihkan data TI lama...');
        DB::table('master_mata_kuliahs')->where('prodi_id', $prodi->id)->delete();

        foreach ($data as $item) {
            $activityType = 'REGULAR';
            if (Str::contains($item['nama'], ['Tugas Akhir', 'Skripsi', 'Kolokium'])) $activityType = 'THESIS';
            elseif (Str::contains($item['nama'], ['Jalur Pilihan', 'Magang', 'MBKM'])) $activityType = 'INTERNSHIP';

            DB::table('master_mata_kuliahs')->insert([
                'prodi_id' => $prodi->id,
                'kode_mk' => $item['kode'],
                'nama_mk' => $item['nama'],
                'sks_default' => $item['jml'],
                'sks_tatap_muka' => $item['tm'],
                'sks_praktek' => $item['p'],
                'sks_lapangan' => $item['l'],
                'activity_type' => $activityType,
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }
        $this->command->info('Berhasil input ' . count($data) . ' MK Master TI.');
    }
}
