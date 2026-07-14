<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MasterMatakuliahK3Seeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Memulai integrasi Master Kurikulum dan Mata Kuliah K3...');

        // 1. Ambil ID Prodi K3 (Keselamatan dan Kesehatan Kerja)
        $prodi = DB::table('ref_prodi')->where('kode_prodi_internal', 'K3')->first();

        if (!$prodi) {
            $this->command->error("Prodi dengan kode 'K3' tidak ditemukan di tabel ref_prodi.");
            return;
        }

        $now = Carbon::now();

        // 2. Buat Data Master Kurikulum (Tabel: master_kurikulums)
        $kurikulumId = DB::table('master_kurikulums')->insertGetId([
            'prodi_id'           => $prodi->id,
            'nama_kurikulum'     => 'Kurikulum K3 2025',
            'tahun_mulai'        => 2025,
            'id_semester_mulai'  => '20251',
            'is_active'          => 1,
            'jumlah_sks_lulus'   => 145,
            'jumlah_sks_wajib'   => 145,
            'jumlah_sks_pilihan' => 0,
            'no_sk_kurikulum'    => '985/E/0/2023',
            'tgl_sk_kurikulum'   => '2023-12-31',
            'keterangan'         => 'Kurikulum operasional perdana Program Studi K3 Universitas Stella Maris Sumba',
            'created_at'         => $now,
            'updated_at'         => $now,
        ]);

        // 3. Daftar Mata Kuliah Riil Berdasarkan Dokumen Sebaran K3
        $sebaranMK = [
            // Semester 1
            1 => [
                ['kode' => 'K3-10001', 'nama' => 'Pendidikan Agama', 't' => 2, 'p' => 0],
                ['kode' => 'K3-10002', 'nama' => 'Pendidikan Pancasila', 't' => 2, 'p' => 0],
                ['kode' => 'K3-10003', 'nama' => 'Pendidikan Anti Korupsi', 't' => 2, 'p' => 0],
                ['kode' => 'K3-10004', 'nama' => 'Bahasa Indonesia', 't' => 2, 'p' => 0],
                ['kode' => 'K3-10005', 'nama' => 'Dasar Dasar Keselamatan dan Kesehatan kerja', 't' => 2, 'p' => 0],
                ['kode' => 'K3-10006', 'nama' => 'Etika dan Hukum Kesehatan', 't' => 2, 'p' => 0],
                ['kode' => 'K3-10007', 'nama' => 'Aspek Manusia Dalam K3', 't' => 2, 'p' => 0],
                ['kode' => 'K3-10008', 'nama' => 'Matematika', 't' => 2, 'p' => 0],
                ['kode' => 'K3-10009', 'nama' => 'Dasar - Dasar Pendidikan Komputer', 't' => 2, 'p' => 0],
                ['kode' => 'K3-10010', 'nama' => 'Ilmu Sosial dan Budaya Dasar', 't' => 2, 'p' => 0],
            ],
            // Semester 2
            2 => [
                ['kode' => 'K3-20001', 'nama' => 'Bahasa Inggris Kesehatan', 't' => 2, 'p' => 0],
                ['kode' => 'K3-20002', 'nama' => 'Kepemimpinan', 't' => 2, 'p' => 0],
                ['kode' => 'K3-20003', 'nama' => 'Pendidikan Kewarganegaraan', 't' => 2, 'p' => 0],
                ['kode' => 'K3-20004', 'nama' => 'Fisika dan Kimia Dalam K3', 't' => 2, 'p' => 0],
                ['kode' => 'K3-20005', 'nama' => 'Psikologi industri', 't' => 2, 'p' => 0],
                ['kode' => 'K3-20006', 'nama' => 'Ergonomi', 't' => 2, 'p' => 0],
                ['kode' => 'K3-20007', 'nama' => 'Kewirausahaan', 't' => 2, 'p' => 0],
                ['kode' => 'K3-20008', 'nama' => 'Anatomi Fisiologi Kerja', 't' => 2, 'p' => 0],
                ['kode' => 'K3-20009', 'nama' => 'Regulasi dan Kebijakan Bidang K3', 't' => 2, 'p' => 0],
                ['kode' => 'K3-20010', 'nama' => 'Pengantar Proses Industri', 't' => 2, 'p' => 0],
            ],
            // Semester 3
            3 => [
                ['kode' => 'K3-30001', 'nama' => 'Fisiologi kerja', 't' => 2, 'p' => 0],
                ['kode' => 'K3-30002', 'nama' => 'Manajemen K3', 't' => 2, 'p' => 0],
                ['kode' => 'K3-31003', 'nama' => 'Manajemen Penanggulangan Bencana dan Sistem Tanggap Darurat', 't' => 1, 'p' => 1],
                ['kode' => 'K3-31004', 'nama' => 'Penilaian Ergonomi', 't' => 2, 'p' => 1],
                ['kode' => 'K3-30005', 'nama' => 'Toksikologi Industri', 't' => 2, 'p' => 0],
                ['kode' => 'K3-30006', 'nama' => 'Epidemiologi', 't' => 2, 'p' => 0],
                ['kode' => 'K3-31007', 'nama' => 'Manajemen Bahaya Fisik', 't' => 2, 'p' => 1],
                ['kode' => 'K3-30008', 'nama' => 'Menajemen Resiko Keselamatan', 't' => 2, 'p' => 0],
                ['kode' => 'K3-30009', 'nama' => 'Manajemen Pengelolaan Limbah', 't' => 2, 'p' => 0],
                ['kode' => 'K3-30010', 'nama' => 'Teknik Identifikasi Bahaya', 't' => 2, 'p' => 0],
            ],
            // Semester 4
            4 => [
                ['kode' => 'K3-41001', 'nama' => 'Manajemen Bahaya Kimia dan Biomonitoring', 't' => 2, 'p' => 1],
                ['kode' => 'K3-40002', 'nama' => 'Gizi Kerja', 't' => 2, 'p' => 0],
                ['kode' => 'K3-41003', 'nama' => 'Hygiene Industri', 't' => 2, 'p' => 1],
                ['kode' => 'K3-40004', 'nama' => 'Keselamatan Kerja B3', 't' => 2, 'p' => 0],
                ['kode' => 'K3-40005', 'nama' => 'Manajemen Kebakaran dan Ledakan', 't' => 2, 'p' => 0],
                ['kode' => 'K3-40006', 'nama' => 'Sistem Manajemen Lingkungan', 't' => 2, 'p' => 0],
                ['kode' => 'K3-41007', 'nama' => 'Biostatistik', 't' => 2, 'p' => 1],
                ['kode' => 'K3-40008', 'nama' => 'Promosi Kesehatan Keselamatan Kerja', 't' => 2, 'p' => 0],
            ],
            // Semester 5
            5 => [
                ['kode' => 'K3-50001', 'nama' => 'Penyakit Akibat Kerja', 't' => 2, 'p' => 0],
                ['kode' => 'K3-51002', 'nama' => 'Metodelogi Penelitian', 't' => 2, 'p' => 1],
                ['kode' => 'K3-51003', 'nama' => 'Laboratorium K3 I', 't' => 0, 'p' => 3],
                ['kode' => 'K3-50004', 'nama' => 'Pemeliharan dan Peningkatan Kesehatan Kerja', 't' => 2, 'p' => 0],
                ['kode' => 'K3-51005', 'nama' => 'Pertolongan Pertama Pada Kecelakaan (P3K)', 't' => 2, 'p' => 1],
                ['kode' => 'K3-51006', 'nama' => 'Keselamatan dan Kesehatan Kerja Industri', 't' => 2, 'p' => 1],
                ['kode' => 'K3-51007', 'nama' => 'Keselamatan dan Kesehatan Kerja Pertanian', 't' => 2, 'p' => 1],
                ['kode' => 'K3-51008', 'nama' => 'Keselamatan dan Kesehatan Kerja Rumah Sakit', 't' => 2, 'p' => 1],
            ],
            // Semester 6
            6 => [
                ['kode' => 'K3-60003', 'nama' => 'Analisis Dampak Masalah Lingkungan', 't' => 2, 'p' => 0],
                ['kode' => 'K3-60005', 'nama' => 'Ventilasi, Sanitasi Industri dan Lingkungan Bersih', 't' => 2, 'p' => 0],
                ['kode' => 'K3-61002', 'nama' => 'Sistem Manajemen Kesehatan Dan Keselamatan Kerja (SMK3)', 't' => 2, 'p' => 1],
                ['kode' => 'K3-61001', 'nama' => 'Laboratorium K3 II', 't' => 0, 'p' => 3],
                ['kode' => 'K3-61004', 'nama' => 'Contractor Safety Management System (CSMS)', 't' => 2, 'p' => 1],
                ['kode' => 'K3-61006', 'nama' => 'Audit Sistem Manajemen Keselamatan Dan Kesehatan Kerja', 't' => 2, 'p' => 1],
                ['kode' => 'K3-61007', 'nama' => 'Keselamatan dan Kesehatan Kerja Kontruksi dan Bangunan', 't' => 2, 'p' => 1],
                ['kode' => 'K3-61008', 'nama' => 'Keselamatan dan Kesehatan Kerja Pariwisata', 't' => 2, 'p' => 1],
            ],
            // Semester 7
            7 => [
                ['kode' => 'K3-71001', 'nama' => 'Magang', 't' => 0, 'p' => 20],
            ],
            // Semester 8
            8 => [
                ['kode' => 'K3-81002', 'nama' => 'Tugas Akhir', 't' => 0, 'p' => 4],
            ],
        ];

        // 4. Proses Seeding Relasi (Mata Kuliah & Kurikulum)
        foreach ($sebaranMK as $semester => $daftarMK) {
            foreach ($daftarMK as $mk) {
                // Tentukan activity_type sesuai instruksi
                $activityType = 'REGULAR';
                if ($mk['kode'] === 'K3-81002') $activityType = 'THESIS';
                if ($mk['kode'] === 'K3-71001') $activityType = 'INTERNSHIP';

                // Insert/Update ke master_mata_kuliahs
                $mkId = DB::table('master_mata_kuliahs')->insertGetId([
                    'prodi_id'       => $prodi->id,
                    'kode_mk'        => $mk['kode'],
                    'nama_mk'        => $mk['nama'],
                    'sks_default'    => $mk['t'] + $mk['p'],
                    'sks_tatap_muka' => $mk['t'],
                    'sks_praktek'    => $mk['p'],
                    'sks_lapangan'   => 0,
                    'jenis_mk'       => 'A',
                    'activity_type'  => $activityType,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);

                // Hubungkan ke kurikulum_mata_kuliah (Tabel junction)
                DB::table('kurikulum_mata_kuliah')->insert([
                    'kurikulum_id'   => $kurikulumId,
                    'mata_kuliah_id' => $mkId,
                    'semester_paket' => $semester,
                    'sks_tatap_muka' => $mk['t'],
                    'sks_praktek'    => $mk['p'],
                    'sks_lapangan'   => 0,
                    'sifat_mk'       => 'W', // Sifat MK Wajib
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
            }
        }

        $this->command->info('Integrasi Kurikulum dan Mata Kuliah Prodi K3 Berhasil!');
    }
}