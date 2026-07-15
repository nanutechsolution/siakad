<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EdomQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat Kelompok Kuisioner terlebih dahulu menggunakan kolom 'nama_kelompok' dan 'kategori' => 'EDOM'
        
        $kelompok1Id = DB::table('lpm_kuisioner_kelompok')->insertGetId([
            'nama_kelompok'     => 'Perencanaan & Kesiapan Pembelajaran',
            'kategori'          => 'EDOM',
            'urutan'            => 1,
            'is_active'         => 1,
            'tahun_akademik_id' => null, // Dibuat null agar tidak bentrok dengan ref_tahun_akademik yang mungkin masih kosong
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $kelompok2Id = DB::table('lpm_kuisioner_kelompok')->insertGetId([
            'nama_kelompok'     => 'Proses Pembelajaran & Penguasaan Materi',
            'kategori'          => 'EDOM',
            'urutan'            => 2,
            'is_active'         => 1,
            'tahun_akademik_id' => null,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $kelompok3Id = DB::table('lpm_kuisioner_kelompok')->insertGetId([
            'nama_kelompok'     => 'Evaluasi & Umpan Balik',
            'kategori'          => 'EDOM',
            'urutan'            => 3,
            'is_active'         => 1,
            'tahun_akademik_id' => null,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $kelompok4Id = DB::table('lpm_kuisioner_kelompok')->insertGetId([
            'nama_kelompok'     => 'Sikap & Kedisiplinan',
            'kategori'          => 'EDOM',
            'urutan'            => 4,
            'is_active'         => 1,
            'tahun_akademik_id' => null,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // 2. Masukkan daftar pertanyaan yang terikat ke ID Kelompok di atas
        $questions = [
            // KELOMPOK 1
            [
                'kelompok_id'      => $kelompok1Id,
                'bunyi_pertanyaan' => 'Dosen menyampaikan Rencana Pembelajaran Semester (RPS) dan kontrak perkuliahan pada pertemuan pertama.',
                'jenis_input'      => 'RATING_4',
                'is_required'      => 1,
                'urutan'           => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'kelompok_id'      => $kelompok1Id,
                'bunyi_pertanyaan' => 'Dosen menjelaskan tujuan pembelajaran, materi, serta sistem penilaian secara jelas.',
                'jenis_input'      => 'RATING_4',
                'is_required'      => 1,
                'urutan'           => 2,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],

            // KELOMPOK 2
            [
                'kelompok_id'      => $kelompok2Id,
                'bunyi_pertanyaan' => 'Dosen menguasai materi perkuliahan dengan baik dan mampu menjelaskannya secara sistematis.',
                'jenis_input'      => 'RATING_4',
                'is_required'      => 1,
                'urutan'           => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'kelompok_id'      => $kelompok2Id,
                'bunyi_pertanyaan' => 'Dosen menggunakan metode pembelajaran yang bervariasi dan memicu keaktifan mahasiswa.',
                'jenis_input'      => 'RATING_4',
                'is_required'      => 1,
                'urutan'           => 2,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'kelompok_id'      => $kelompok2Id,
                'bunyi_pertanyaan' => 'Dosen memanfaatkan media atau sarana pembelajaran (seperti LCD, LMS, modul, dll) secara efektif.',
                'jenis_input'      => 'RATING_4',
                'is_required'      => 1,
                'urutan'           => 3,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],

            // KELOMPOK 3
            [
                'kelompok_id'      => $kelompok3Id,
                'bunyi_pertanyaan' => 'Dosen memberikan umpan balik (feedback) atau pembahasan atas tugas, kuis, atau ujian.',
                'jenis_input'      => 'RATING_4',
                'is_required'      => 1,
                'urutan'           => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'kelompok_id'      => $kelompok3Id,
                'bunyi_pertanyaan' => 'Dosen memberikan penilaian secara objektif, transparan, dan adil sesuai kontrak kuliah.',
                'jenis_input'      => 'RATING_4',
                'is_required'      => 1,
                'urutan'           => 2,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],

            // KELOMPOK 4
            [
                'kelompok_id'      => $kelompok4Id,
                'bunyi_pertanyaan' => 'Dosen hadir tepat waktu sesuai dengan jadwal perkuliahan yang disepakati.',
                'jenis_input'      => 'RATING_4',
                'is_required'      => 1,
                'urutan'           => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'kelompok_id'      => $kelompok4Id,
                'bunyi_pertanyaan' => 'Dosen menunjukkan sikap ramah, menghargai perbedaan pendapat, dan terbuka untuk berdiskusi.',
                'jenis_input'      => 'RATING_4',
                'is_required'      => 1,
                'urutan'           => 2,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ];

        DB::table('lpm_kuisioner_pertanyaan')->insert($questions);
    }
}