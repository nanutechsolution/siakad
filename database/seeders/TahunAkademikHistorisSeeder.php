<?php

namespace Database\Seeders;

use App\Models\RefTahunAkademik;
use Illuminate\Database\Seeder;

/**
 * php artisan db:seed --class=TahunAkademikHistorisSeeder
 *
 * Semester HISTORIS (sudah lewat) diseed langsung via importHistorical() —
 * status "Selesai", tidak perlu dijalani lewat Buka KRS/Publish Nilai satu-satu.
 *
 * Semester yang SEDANG BERJALAN sekarang (20261) SENGAJA TIDAK ada di sini —
 * buat itu lewat halaman "Buat Draft Semester" di UI, lalu jalankan workflow
 * asli (Buka KRS -> ... -> Publish Nilai) supaya statusnya benar-benar
 * mencerminkan proses yang sedang terjadi.
 */
class TahunAkademikHistorisSeeder extends Seeder
{
    public function run(): void
    {
        $semesterHistoris = [
            [
                'kode_tahun' => '20241',
                'nama_tahun' => 'Ganjil 2024/2025',
                'semester' => 1,
                'tanggal_mulai' => '2024-08-01',
                'tanggal_selesai' => '2024-12-20',
                'tgl_publish_nilai' => '2025-01-10',
            ],
            [
                'kode_tahun' => '20242',
                'nama_tahun' => 'Genap 2024/2025',
                'semester' => 2,
                'tanggal_mulai' => '2025-02-01',
                'tanggal_selesai' => '2025-06-20',
                'tgl_publish_nilai' => '2025-07-05',
            ],
            [
                'kode_tahun' => '20251',
                'nama_tahun' => 'Ganjil 2025/2026',
                'semester' => 1,
                'tanggal_mulai' => '2025-08-01',
                'tanggal_selesai' => '2025-12-20',
                'tgl_publish_nilai' => '2026-01-10',
            ],
            [
                'kode_tahun' => '20252',
                'nama_tahun' => 'Genap 2025/2026',
                'semester' => 2,
                'tanggal_mulai' => '2026-02-01',
                'tanggal_selesai' => '2026-06-20',
                'tgl_publish_nilai' => '2026-07-05',
            ],
            // Tambahkan baris lain di sini kalau ada angkatan lebih tua dari 2024.
        ];

        foreach ($semesterHistoris as $row) {
            if (RefTahunAkademik::where('kode_tahun', $row['kode_tahun'])->exists()) {
                $this->command->line("  - {$row['kode_tahun']} sudah ada, dilewati.");
                continue;
            }

            RefTahunAkademik::importHistorical(array_merge($row, [
                'buka_krs' => false,
                'is_locked_krs' => true,
                'buka_input_nilai' => false,
                'is_locked_nilai' => true,
            ]));

            $this->command->info("  + {$row['kode_tahun']} — {$row['nama_tahun']} (Selesai)");
        }

        $this->command->newLine();
        $this->command->warn(
            'Semester yang sedang berjalan (20261) TIDAK diseed di sini — '
                . 'buat lewat UI "Buat Draft Semester" lalu jalankan workflow guided-nya.'
        );
    }
}
