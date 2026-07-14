<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MasterMatakuliahTLSeeder extends Seeder
{
    public function run(): void
    {
        $prodi = DB::table('ref_prodi')->where('kode_prodi_internal', 'TL')->first();
        if (!$prodi) return;

        $now = Carbon::now();
        $courses = [
            // SEMESTER I
            ['kode_mk' => 'MKWN2530201', 'nama_mk' => 'Pendidikan Agama', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKWN2530202', 'nama_mk' => 'Pendidikan Pancasila', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKWN2530203', 'nama_mk' => 'Pendidikan Anti Korupsi', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKWN2530204', 'nama_mk' => 'Bahasa Indonesia', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530201', 'nama_mk' => 'Fisika Dasar 1', 'sks_t' => 2, 'sks_p' => 1],
            ['kode_mk' => 'MKTL2530202', 'nama_mk' => 'Biologi Lingkungan', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530203', 'nama_mk' => 'Kimia Dasar 1', 'sks_t' => 2, 'sks_p' => 1],
            ['kode_mk' => 'MKTL2530204', 'nama_mk' => 'Matematika Dasar', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530205', 'nama_mk' => 'Pengenalan Lingkungan Potensi Sumba Barat Daya', 'sks_t' => 2, 'sks_p' => 0],
            // SEMESTER II
            ['kode_mk' => 'MKWN2530205', 'nama_mk' => 'Kewarganegaraan', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530206', 'nama_mk' => 'Gambar Teknik', 'sks_t' => 2, 'sks_p' => 1],
            ['kode_mk' => 'MKTL2530207', 'nama_mk' => 'Fisika Dasar II', 'sks_t' => 2, 'sks_p' => 1],
            ['kode_mk' => 'MKTL2530208', 'nama_mk' => 'Kimia Dasar II', 'sks_t' => 2, 'sks_p' => 1],
            ['kode_mk' => 'MKTL2530209', 'nama_mk' => 'Hukum dan Kebijakan Lingkungan', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530210', 'nama_mk' => 'Mikrobiologi Lingkungan', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530211', 'nama_mk' => 'Pengelolaan Sumber Daya Air', 'sks_t' => 3, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530212', 'nama_mk' => 'Pencemaran Udara', 'sks_t' => 2, 'sks_p' => 0],
            // SEMESTER III
            ['kode_mk' => 'MKTL2530213', 'nama_mk' => 'Perencanaan Struktur', 'sks_t' => 3, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530214', 'nama_mk' => 'Mekanika Tanah dan Pondasi', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530215', 'nama_mk' => 'Kesehatan Lingkungan', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530216', 'nama_mk' => 'Hidrolika', 'sks_t' => 2, 'sks_p' => 1],
            ['kode_mk' => 'MKTL2530217', 'nama_mk' => 'Perpetaan', 'sks_t' => 2, 'sks_p' => 1],
            ['kode_mk' => 'MKTL2530218', 'nama_mk' => 'Teknik Analisis Pencemaran Lingkungan', 'sks_t' => 3, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530219', 'nama_mk' => 'Etika Lingkungan dan Profesi', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530220', 'nama_mk' => 'Geohidrologi', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530221', 'nama_mk' => 'Konservasi Sumber Daya Alam', 'sks_t' => 2, 'sks_p' => 0],
            // SEMESTER IV
            ['kode_mk' => 'MKTL2530222', 'nama_mk' => 'Manajemen SDA dan Lingkungan', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530223', 'nama_mk' => 'Sistem Penyaluran Air Limbah', 'sks_t' => 2, 'sks_p' => 1],
            ['kode_mk' => 'MKTL2530224', 'nama_mk' => 'Sistem Penyediaan Air Minum', 'sks_t' => 2, 'sks_p' => 1],
            ['kode_mk' => 'MKTL2530225', 'nama_mk' => 'Pengelolaan Sampah', 'sks_t' => 2, 'sks_p' => 1],
            ['kode_mk' => 'MKTL2530226', 'nama_mk' => 'Statistik Lingkungan', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530227', 'nama_mk' => 'Kewirausahaan', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530228', 'nama_mk' => 'Kesehatan dan Keselamatan Kerja', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530229', 'nama_mk' => 'Sistem Manajemen Lingkungan', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530230', 'nama_mk' => 'Pengendalian dan Emisi Ambien', 'sks_t' => 3, 'sks_p' => 0],
            // SEMESTER V
            ['kode_mk' => 'MKTL2530231', 'nama_mk' => 'Sanitasi Lingkungan', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530232', 'nama_mk' => 'Unit Operasi Teknik Lingkungan', 'sks_t' => 3, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530233', 'nama_mk' => 'Unit Proses Teknik Lingkungan', 'sks_t' => 2, 'sks_p' => 1],
            ['kode_mk' => 'MKTL2530234', 'nama_mk' => 'Sistem Drainase Perkotaan', 'sks_t' => 2, 'sks_p' => 1],
            ['kode_mk' => 'MKTL2530235', 'nama_mk' => 'Metodologi Penelitian', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530236', 'nama_mk' => 'Pencemaran Tanah Dan Air Tanah', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530237', 'nama_mk' => 'Mitigasi Bencana', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530238', 'nama_mk' => 'Teknologi Pengolahan Limbah Industri', 'sks_t' => 2, 'sks_p' => 1],
            ['kode_mk' => 'MKTL2530239', 'nama_mk' => 'Teknologi Hijau', 'sks_t' => 2, 'sks_p' => 0],
            // SEMESTER VI
            ['kode_mk' => 'MKTL2530240', 'nama_mk' => 'Plambing dan Instrumentasi', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530241', 'nama_mk' => 'Analisis Mengenai Dampak Lingkungan', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530242', 'nama_mk' => 'Pengelolaan Limbah B3', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530243', 'nama_mk' => 'Pengelolaan Limbah Industri', 'sks_t' => 2, 'sks_p' => 1],
            ['kode_mk' => 'MKTL2530244', 'nama_mk' => 'Pengendalian Pencemaran Air', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530245', 'nama_mk' => 'Audit Lingkungan', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530246', 'nama_mk' => 'Perencanaan Bangunan Pengelolaan Air Limbah', 'sks_t' => 2, 'sks_p' => 1],
            ['kode_mk' => 'MKTL2530247', 'nama_mk' => 'Perencanaan dan Pengelolaan Proyek', 'sks_t' => 3, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530248', 'nama_mk' => 'Teknologi Energi Terbarukan', 'sks_t' => 2, 'sks_p' => 1],
            // SEMESTER VII
            ['kode_mk' => 'MKTL2530249', 'nama_mk' => 'Kerja Praktik', 'sks_t' => 8, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530250', 'nama_mk' => 'Proposal', 'sks_t' => 2, 'sks_p' => 0],
            // SEMESTER VIII
            ['kode_mk' => 'MKTL2530251', 'nama_mk' => 'Seminar Proposal', 'sks_t' => 2, 'sks_p' => 0],
            ['kode_mk' => 'MKTL2530252', 'nama_mk' => 'SKRIPSI', 'sks_t' => 4, 'sks_p' => 0],
        ];

        foreach ($courses as $course) {
            DB::table('master_mata_kuliahs')->updateOrInsert(
                ['prodi_id' => $prodi->id, 'kode_mk' => $course['kode_mk']],
                [
                    'nama_mk'        => $course['nama_mk'],
                    'sks_default'    => $course['sks_t'] + $course['sks_p'],
                    'sks_tatap_muka' => $course['sks_t'],
                    'sks_praktek'    => $course['sks_p'],
                    'sks_lapangan'   => 0,
                    'jenis_mk'       => 'A',
                    'activity_type'  => 'REGULAR',
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]
            );
        }
    }
}