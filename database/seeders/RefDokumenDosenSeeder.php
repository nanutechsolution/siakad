<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RefDokumenDosenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $dokumen = [
            [
                'kode' => 'ktp',
                'nama_dokumen' => 'Scan Kartu Identitas (KTP)',
                'allowed_types' => 'jpg,jpeg,png,pdf',
                'max_size_kb' => 1024, // 1MB KTP cukup
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode' => 'ijazah',
                'nama_dokumen' => 'Ijazah Terakhir (Scan Asli)',
                'allowed_types' => 'pdf', // Ijazah wajib PDF agar formal
                'max_size_kb' => 5120, // 5MB karena mungkin scan berwarna resolusi tinggi
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode' => 'serdos',
                'nama_dokumen' => 'Sertifikat Pendidik (Serdos)',
                'allowed_types' => 'pdf',
                'max_size_kb' => 2048, // 2MB
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode' => 'sk',
                'nama_dokumen' => 'SK Penugasan / Kepegawaian',
                'allowed_types' => 'pdf',
                'max_size_kb' => 2048,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode' => 'pelatihan',
                'nama_dokumen' => 'Sertifikat Kompetensi / Pelatihan',
                'allowed_types' => 'pdf,jpg,jpeg,png',
                'max_size_kb' => 2048,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('ref_dokumen_dosen')->insert($dokumen);
    }
}