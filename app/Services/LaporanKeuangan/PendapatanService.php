<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan;

use App\Services\LaporanKeuangan\Support\MahasiswaInfoQuery;
use App\Services\LaporanKeuangan\Support\TagihanMapQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Digunakan oleh 3 halaman: Pendapatan Mahasiswa, Pendapatan Per Prodi,
 * Pendapatan Per Periode. Setiap halaman punya bentuk agregasi berbeda,
 * jadi ada 3 method query*() terpisah — semuanya mengembalikan Builder
 * (belum dieksekusi), dipaginate/di-chunk oleh trait seperti laporan lain.
 *
 * ATURAN BISNIS: hanya pembayaran dengan status verifikasi FINAL
 * (`ref_status_verifikasi_pembayaran.is_final = 1`) yang dihitung sebagai
 * pendapatan riil (prinsip pengakuan pendapatan).
 */
final class PendapatanService
{
    private function verifiedPaymentsQuery(array $filters): Builder
    {
        $map = TagihanMapQuery::build();

        $query = MahasiswaInfoQuery::base()
            ->joinSub($map, 'tm', fn($join) => $join->on('tm.mahasiswa_id', '=', 'mahasiswas.id'))
            ->join('pembayaran_mahasiswas as pm', 'pm.tagihan_id', '=', 'tm.tagihan_id')
            ->join('ref_status_verifikasi_pembayaran as sv', 'sv.id', '=', 'pm.status_verifikasi_id')
            ->leftJoin('ref_tahun_akademik as ta', 'ta.id', '=', 'tm.tahun_akademik_id')
            ->whereNull('pm.deleted_at')
            ->where('sv.is_final', true)
            ->when($filters['tahun_akademik_id'] ?? null, fn($q, $v) => $q->where('tm.tahun_akademik_id', $v))
            ->when($filters['tanggal_dari'] ?? null, fn($q, $v) => $q->whereDate('pm.tanggal_bayar', '>=', $v))
            ->when($filters['tanggal_sampai'] ?? null, fn($q, $v) => $q->whereDate('pm.tanggal_bayar', '<=', $v));

        return MahasiswaInfoQuery::applyFilters($query, $filters);
    }

    /** Dipakai widget Stat "Total Pendapatan" — 1 angka, aman langsung ->sum(). */
    public function totalPendapatan(array $filters): float
    {
        return (float) $this->verifiedPaymentsQuery($filters)->sum('pm.nominal_bayar');
    }

    /** Dipakai widget Chart trend — hasil dikelompokkan per bulan, dataset kecil. */
    public function trendBulanan(array $filters): Collection
    {
        return $this->verifiedPaymentsQuery($filters)
            ->selectRaw("DATE_FORMAT(pm.tanggal_bayar, '%Y-%m') as bulan")
            ->selectRaw('SUM(pm.nominal_bayar) as total')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();
    }

    /** Laporan #5 — Pendapatan Mahasiswa: pendapatan per jenis tagihan. */
    public function queryPerJenisTagihan(array $filters): Builder
    {
        return $this->verifiedPaymentsQuery($filters)
            ->select('tm.jenis_tagihan')
            ->selectRaw('SUM(pm.nominal_bayar) as total')
            ->groupBy('tm.jenis_tagihan')
            ->orderBy('tm.jenis_tagihan');
    }

    /**
     * Laporan #6 — Pendapatan Per Prodi.
     *
     * Dibangun sebagai SATU query (bukan dua query yang digabung di PHP
     * seperti versi sebelumnya) — pendapatan-per-prodi dihitung via
     * subquery teragregasi yang di-LEFT JOIN, bukan loop PHP.
     */
    public function queryPerProdi(array $filters): Builder
    {
        $map = TagihanMapQuery::build();

        $pendapatanPerProdi = MahasiswaInfoQuery::base()
            ->joinSub($map, 'tm', fn($join) => $join->on('tm.mahasiswa_id', '=', 'mahasiswas.id'))
            ->join('pembayaran_mahasiswas as pm', 'pm.tagihan_id', '=', 'tm.tagihan_id')
            ->join('ref_status_verifikasi_pembayaran as sv', 'sv.id', '=', 'pm.status_verifikasi_id')
            ->whereNull('pm.deleted_at')
            ->where('sv.is_final', true)
            ->when($filters['tahun_akademik_id'] ?? null, fn($q, $v) => $q->where('tm.tahun_akademik_id', $v))
            ->when($filters['tanggal_dari'] ?? null, fn($q, $v) => $q->whereDate('pm.tanggal_bayar', '>=', $v))
            ->when($filters['tanggal_sampai'] ?? null, fn($q, $v) => $q->whereDate('pm.tanggal_bayar', '<=', $v))
            ->groupBy('pr.id')
            ->selectRaw('pr.id as prodi_id, SUM(pm.nominal_bayar) as total_pendapatan');

        $query = MahasiswaInfoQuery::base()
            ->joinSub($map, 'tm', fn($join) => $join->on('tm.mahasiswa_id', '=', 'mahasiswas.id'))
            ->when($filters['tahun_akademik_id'] ?? null, fn($q, $v) => $q->where('tm.tahun_akademik_id', $v));

        $query = MahasiswaInfoQuery::applyFilters($query, $filters);

        return $query
            ->leftJoinSub($pendapatanPerProdi, 'pp', fn($join) => $join->on('pp.prodi_id', '=', 'pr.id'))
            ->groupBy('pr.id', 'pr.nama_prodi', 'pp.total_pendapatan')
            ->orderBy('pr.nama_prodi')
            ->selectRaw('
        pr.id as id,
        pr.id as prodi_id,
        pr.nama_prodi,
        COUNT(DISTINCT mahasiswas.id) as jumlah_mahasiswa,
        SUM(tm.total_tagihan) as total_tagihan,
        SUM(tm.total_bayar) as total_pembayaran,
        COALESCE(pp.total_pendapatan, 0) as total_pendapatan
    ');
    }

    /** Laporan #7 — Pendapatan Per Periode (bulanan / semester / tahun akademik). */
    public function queryPerPeriode(array $filters, string $groupBy = 'bulanan'): Builder
    {
        $query = $this->verifiedPaymentsQuery($filters);

        return match ($groupBy) {
            'tahun_akademik' => $query
                ->select('ta.id as periode_id', 'ta.nama_tahun as label')
                ->selectRaw('SUM(pm.nominal_bayar) as total')
                ->groupBy('ta.id', 'ta.nama_tahun')
                ->orderBy('ta.id'),
            'semester' => $query
                ->select('ta.semester as periode_id')
                ->selectRaw("CASE ta.semester WHEN 1 THEN 'Ganjil' WHEN 2 THEN 'Genap' ELSE 'Pendek' END as label")
                ->selectRaw('SUM(pm.nominal_bayar) as total')
                ->groupBy('ta.semester')
                ->orderBy('ta.semester'),
            default => $query
                ->selectRaw("DATE_FORMAT(pm.tanggal_bayar, '%Y-%m') as periode_id")
                ->selectRaw("DATE_FORMAT(pm.tanggal_bayar, '%Y-%m') as label")
                ->selectRaw('SUM(pm.nominal_bayar) as total')
                ->groupBy('periode_id', 'label')
                ->orderBy('periode_id'),
        };
    }

    /** Dipakai widget Chart — hasil per periode selalu dataset kecil (puluhan baris), aman ->get(). */
    public function perPeriode(array $filters, string $groupBy = 'bulanan'): Collection
    {
        return $this->queryPerPeriode($filters, $groupBy)->get();
    }
}
