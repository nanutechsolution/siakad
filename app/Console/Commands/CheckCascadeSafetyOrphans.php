<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * php artisan audit:check-orphans
 *
 * Mengecek data "yatim" (baris child yang parent_id-nya sudah tidak ada)
 * untuk SELURUH relasi yang diubah menjadi RESTRICT di Migration 1-9.
 *
 * WAJIB dijalankan dan harus lulus (exit code 0 / "Tidak ditemukan data
 * yatim") SEBELUM migration 1-9 dijalankan di database produksi. Jika ada
 * baris [ORPHAN], investigasi dan putuskan: hapus manual data rusak
 * tersebut, atau perbaiki referensinya, SEBELUM melanjutkan migration.
 */
class CheckCascadeSafetyOrphans extends Command
{
    protected $signature = 'audit:check-orphans {--fix-report= : Simpan hasil ke file (path), untuk lampiran sebelum migrasi produksi}';

    protected $description = 'Cek data yatim pada seluruh relasi yang akan diubah menjadi RESTRICT (deletion safety audit, Migration 1-9)';

    /**
     * [child_table, child_fk_column, parent_table, parent_pk_column, label, sumber_migration]
     */
    protected array $checks = [
        // Migration 1
        ['mahasiswas', 'person_id', 'ref_person', 'id', 'mahasiswas -> ref_person', 'M1'],
        ['trx_dosen', 'person_id', 'ref_person', 'id', 'trx_dosen -> ref_person', 'M1'],

        // Migration 2
        ['akademik_transkrip', 'mahasiswa_id', 'mahasiswas', 'id', 'akademik_transkrip -> mahasiswas', 'M2'],
        ['riwayat_status_mahasiswas', 'mahasiswa_id', 'mahasiswas', 'id', 'riwayat_status_mahasiswas -> mahasiswas', 'M2'],
        ['riwayat_prodi_mahasiswas', 'mahasiswa_id', 'mahasiswas', 'id', 'riwayat_prodi_mahasiswas -> mahasiswas', 'M2'],
        ['mahasiswa_kelas', 'mahasiswa_id', 'mahasiswas', 'id', 'mahasiswa_kelas -> mahasiswas', 'M2'],
        ['mahasiswa_kelas', 'kelas_id', 'kelas', 'id', 'mahasiswa_kelas -> kelas', 'M2'],
        ['academic_history_logs', 'mahasiswa_id', 'mahasiswas', 'id', 'academic_history_logs -> mahasiswas', 'M2'],
        ['academic_history_logs', 'tahun_akademik_id', 'ref_tahun_akademik', 'id', 'academic_history_logs -> ref_tahun_akademik', 'M2'],
        ['keuangan_saldos', 'mahasiswa_id', 'mahasiswas', 'id', 'keuangan_saldos -> mahasiswas', 'M2'],
        ['keuangan_mahasiswa_beasiswas', 'mahasiswa_id', 'mahasiswas', 'id', 'keuangan_mahasiswa_beasiswas -> mahasiswas', 'M2'],

        // Migration 3
        ['krs_detail', 'krs_id', 'krs', 'id', 'krs_detail -> krs', 'M3'],
        ['krs_detail_nilai', 'krs_detail_id', 'krs_detail', 'id', 'krs_detail_nilai -> krs_detail', 'M3'],
        ['perkuliahan_absensi', 'krs_detail_id', 'krs_detail', 'id', 'perkuliahan_absensi -> krs_detail', 'M3'],
        ['akademik_grade_revision_logs', 'krs_detail_id', 'krs_detail', 'id', 'akademik_grade_revision_logs -> krs_detail', 'M3'],

        // Migration 4
        ['jadwal_kuliah', 'kelas_id', 'kelas', 'id', 'jadwal_kuliah -> kelas', 'M4'],
        ['perkuliahan_sesi', 'jadwal_kuliah_id', 'jadwal_kuliah', 'id', 'perkuliahan_sesi -> jadwal_kuliah', 'M4'],
        ['jadwal_ujians', 'jadwal_kuliah_id', 'jadwal_kuliah', 'id', 'jadwal_ujians -> jadwal_kuliah', 'M4'],

        // Migration 5
        ['pembimbing_akademik', 'dosen_id', 'trx_dosen', 'id', 'pembimbing_akademik -> trx_dosen', 'M5'],

        // Migration 6
        ['krs_detail', 'mata_kuliah_id', 'master_mata_kuliahs', 'id', 'krs_detail -> master_mata_kuliahs', 'M6'],

        // Migration 8
        ['tagihan_non_regulers', 'mahasiswa_id', 'mahasiswas', 'id', 'tagihan_non_regulers -> mahasiswas', 'M8'],
        ['tagihan_mahasiswas_details', 'tagihan_id', 'tagihan_mahasiswas', 'id', 'tagihan_mahasiswas_details -> tagihan_mahasiswas', 'M8'],
        ['keuangan_adjustments', 'tagihan_id', 'tagihan_mahasiswas', 'id', 'keuangan_adjustments -> tagihan_mahasiswas', 'M8'],
        ['tagihan_non_reguler_details', 'tagihan_id', 'tagihan_non_regulers', 'id', 'tagihan_non_reguler_details -> tagihan_non_regulers', 'M8'],

        // Migration 9
        ['jadwal_kuliah_dosen', 'jadwal_kuliah_id', 'jadwal_kuliah', 'id', 'jadwal_kuliah_dosen -> jadwal_kuliah', 'M9'],
        ['jadwal_ujian_pesertas', 'krs_detail_id', 'krs_detail', 'id', 'jadwal_ujian_pesertas -> krs_detail', 'M9'],
        ['generator_logs', 'mahasiswa_id', 'mahasiswas', 'id', 'generator_logs -> mahasiswas', 'M9'],
        ['sinkronisasi_logs', 'mahasiswa_id', 'mahasiswas', 'id', 'sinkronisasi_logs -> mahasiswas', 'M9'],
        ['sinkronisasi_review_items', 'mahasiswa_id', 'mahasiswas', 'id', 'sinkronisasi_review_items -> mahasiswas', 'M9'],
    ];

    public function handle(): int
    {
        $this->info('Mengecek data yatim pada seluruh relasi yang akan diubah menjadi RESTRICT (Migration 1-9)...');
        $this->newLine();

        $foundIssues = false;
        $report = [];

        foreach ($this->checks as [$child, $childCol, $parent, $parentCol, $label, $source]) {
            $count = DB::table($child . ' as c')
                ->leftJoin($parent . ' as p', 'p.' . $parentCol, '=', 'c.' . $childCol)
                ->whereNotNull('c.' . $childCol)
                ->whereNull('p.' . $parentCol)
                ->count();

            $line = sprintf('[%s] %s', $source, $label);

            if ($count > 0) {
                $foundIssues = true;
                $this->error("[ORPHAN] {$line}: {$count} baris tidak punya parent yang valid.");
                $report[] = "ORPHAN | {$line} | {$count} baris";
            } else {
                $this->line("[OK] {$line}");
                $report[] = "OK | {$line}";
            }
        }

        $this->newLine();

        if ($path = $this->option('fix-report')) {
            file_put_contents($path, implode(PHP_EOL, $report) . PHP_EOL);
            $this->info("Laporan disimpan ke: {$path}");
        }

        if ($foundIssues) {
            $this->warn('Ditemukan data yatim. Investigasi dan bersihkan/perbaiki data di atas SEBELUM menjalankan migration RESTRICT terkait, karena data yatim ini akan tetap ada (tersembunyi) meskipun constraint baru berhasil dibuat.');

            return self::FAILURE;
        }

        $this->info('Tidak ditemukan data yatim. Aman untuk melanjutkan ke migration 1-9.');

        return self::SUCCESS;
    }
}
