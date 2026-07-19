<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan;

use App\Services\LaporanKeuangan\Support\MahasiswaInfoQuery;
use App\Services\LaporanKeuangan\Support\TagihanMapQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Laporan #1 — Rekap Tagihan Mahasiswa.
 *
 * Filter yang didukung (semua opsional):
 * - tahun_akademik_id (hanya berlaku untuk jenis SEMESTER)
 * - semester            (hanya berlaku untuk jenis SEMESTER)
 * - fakultas_id
 * - prodi_id
 * - angkatan_id
 * - jenis_tagihan       SEMESTER | NON_REGULER (kosong = keduanya)
 *
 * CATATAN: `tagihan_non_regulers` tidak memiliki relasi ke
 * `ref_tahun_akademik`, sehingga filter tahun_akademik_id / semester
 * otomatis diabaikan untuk baris NON_REGULER (keterbatasan schema,
 * sudah dikonfirmasi pada tahap analisa).
 */
final class RekapTagihanService
{
    public function rows(array $filters): Collection
    {
        $jenis = $filters['jenis_tagihan'] ?? null;

        $rows = collect();

        if ($jenis === null || $jenis === TagihanMapQuery::JENIS_SEMESTER) {
            $rows = $rows->concat($this->semesterRows($filters));
        }

        if ($jenis === null || $jenis === TagihanMapQuery::JENIS_NON_REGULER) {
            $rows = $rows->concat($this->nonRegulerRows($filters));
        }

        return $rows->values();
    }

    private function semesterRows(array $filters): Collection
    {
        $query = MahasiswaInfoQuery::base()
            ->join('tagihan_mahasiswas as t', 't.mahasiswa_id', '=', 'm.id')
            ->join('ref_tahun_akademik as ta', 'ta.id', '=', 't.tahun_akademik_id')
            ->whereNull('t.deleted_at')
            ->when($filters['tahun_akademik_id'] ?? null, fn($q, $v) => $q->where('t.tahun_akademik_id', $v))
            ->when($filters['semester'] ?? null, fn($q, $v) => $q->where('ta.semester', $v));

        $query = MahasiswaInfoQuery::applyFilters($query, $filters);

        return $query
            ->select([
                'm.nim',
                'p.nama_lengkap',
                'pr.nama_prodi',
                'm.angkatan_id',
                DB::raw("'" . TagihanMapQuery::JENIS_SEMESTER . "' as jenis_tagihan"),
                'ta.nama_tahun as periode',
                't.total_tagihan',
                't.total_bayar',
                't.sisa_tagihan',
                't.status_bayar',
            ])
            ->orderBy('p.nama_lengkap')
            ->get();
    }

    private function nonRegulerRows(array $filters): Collection
    {
        $query = MahasiswaInfoQuery::base()
            ->join('tagihan_non_regulers as t', 't.mahasiswa_id', '=', 'm.id')
            ->whereNull('t.deleted_at');

        $query = MahasiswaInfoQuery::applyFilters($query, $filters);

        return $query
            ->select([
                'm.nim',
                'p.nama_lengkap',
                'pr.nama_prodi',
                'm.angkatan_id',
                DB::raw("'" . TagihanMapQuery::JENIS_NON_REGULER . "' as jenis_tagihan"),
                't.deskripsi as periode',
                't.total_tagihan',
                't.total_bayar',
                DB::raw('(t.total_tagihan - t.total_bayar) as sisa_tagihan'),
                't.status_bayar',
            ])
            ->orderBy('p.nama_lengkap')
            ->get();
    }
}
