<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan;

use App\Services\LaporanKeuangan\Support\MahasiswaInfoQuery;
use App\Services\LaporanKeuangan\Support\TagihanMapQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Digunakan oleh 3 halaman: Pendapatan Mahasiswa, Pendapatan Per Prodi,
 * Pendapatan Per Periode.
 *
 * ATURAN BISNIS: hanya pembayaran dengan status verifikasi FINAL
 * (`ref_status_verifikasi_pembayaran.is_final = 1`) yang dihitung sebagai
 * pendapatan riil (prinsip pengakuan pendapatan). Pembayaran yang masih
 * PENDING/DITOLAK tidak masuk hitungan.
 */
final class PendapatanService
{
    private function verifiedPaymentsQuery(array $filters)
    {
        $map = TagihanMapQuery::build();

        $query = MahasiswaInfoQuery::base()
            ->joinSub($map, 'tm', fn ($join) => $join->on('tm.mahasiswa_id', '=', 'm.id'))
            ->join('pembayaran_mahasiswas as pm', 'pm.tagihan_id', '=', 'tm.tagihan_id')
            ->join('ref_status_verifikasi_pembayaran as sv', 'sv.id', '=', 'pm.status_verifikasi_id')
            ->leftJoin('ref_tahun_akademik as ta', 'ta.id', '=', 'tm.tahun_akademik_id')
            ->whereNull('pm.deleted_at')
            ->where('sv.is_final', true)
            ->when($filters['tahun_akademik_id'] ?? null, fn ($q, $v) => $q->where('tm.tahun_akademik_id', $v))
            ->when($filters['tanggal_dari'] ?? null, fn ($q, $v) => $q->whereDate('pm.tanggal_bayar', '>=', $v))
            ->when($filters['tanggal_sampai'] ?? null, fn ($q, $v) => $q->whereDate('pm.tanggal_bayar', '<=', $v));

        return MahasiswaInfoQuery::applyFilters($query, $filters);
    }

    /** Laporan #5: total pendapatan keseluruhan sesuai filter. */
    public function totalPendapatan(array $filters): float
    {
        return (float) $this->verifiedPaymentsQuery($filters)->sum('pm.nominal_bayar');
    }

    /** Laporan #5: pendapatan per jenis tagihan (SEMESTER vs NON_REGULER). */
    public function perJenisTagihan(array $filters): Collection
    {
        return $this->verifiedPaymentsQuery($filters)
            ->select('tm.jenis_tagihan')
            ->selectRaw('SUM(pm.nominal_bayar) as total')
            ->groupBy('tm.jenis_tagihan')
            ->get();
    }

    /** Laporan #5: trend pendapatan bulanan (12 bulan terakhir dari filter tanggal, atau semua data). */
    public function trendBulanan(array $filters): Collection
    {
        return $this->verifiedPaymentsQuery($filters)
            ->selectRaw("DATE_FORMAT(pm.tanggal_bayar, '%Y-%m') as bulan")
            ->selectRaw('SUM(pm.nominal_bayar) as total')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();
    }

    /** Laporan #6: Pendapatan Per Prodi. */
    public function perProdi(array $filters): Collection
    {
        $map = TagihanMapQuery::build();

        // Total tagihan & jumlah mahasiswa dihitung dari sisi tagihan
        // (independen dari status verifikasi pembayaran).
        $tagihanQuery = MahasiswaInfoQuery::base()
            ->joinSub($map, 'tm', fn ($join) => $join->on('tm.mahasiswa_id', '=', 'm.id'))
            ->when($filters['tahun_akademik_id'] ?? null, fn ($q, $v) => $q->where('tm.tahun_akademik_id', $v));

        $tagihanQuery = MahasiswaInfoQuery::applyFilters($tagihanQuery, $filters);

        $perProdiTagihan = $tagihanQuery
            ->select('pr.id as prodi_id', 'pr.nama_prodi')
            ->selectRaw('COUNT(DISTINCT m.id) as jumlah_mahasiswa')
            ->selectRaw('SUM(tm.total_tagihan) as total_tagihan')
            ->selectRaw('SUM(tm.total_bayar) as total_pembayaran')
            ->groupBy('pr.id', 'pr.nama_prodi')
            ->get()
            ->keyBy('prodi_id');

        $perProdiPendapatan = $this->verifiedPaymentsQuery($filters)
            ->select('pr.id as prodi_id')
            ->selectRaw('SUM(pm.nominal_bayar) as total_pendapatan')
            ->groupBy('pr.id')
            ->get()
            ->keyBy('prodi_id');

        return $perProdiTagihan->map(function (\stdClass $row) use ($perProdiPendapatan) {
            $row->total_pendapatan = (float) ($perProdiPendapatan->get($row->prodi_id)->total_pendapatan ?? 0);

            return $row;
        })->values();
    }

    /** Laporan #7: Pendapatan Per Periode (bulanan / semester / tahun akademik). */
    public function perPeriode(array $filters, string $groupBy = 'bulanan'): Collection
    {
        $query = $this->verifiedPaymentsQuery($filters);

        return match ($groupBy) {
            'tahun_akademik' => $query
                ->select('ta.id as periode_id', 'ta.nama_tahun as label')
                ->selectRaw('SUM(pm.nominal_bayar) as total')
                ->groupBy('ta.id', 'ta.nama_tahun')
                ->orderBy('ta.id')
                ->get(),
            'semester' => $query
                ->select('ta.semester as periode_id')
                ->selectRaw("CASE ta.semester WHEN 1 THEN 'Ganjil' WHEN 2 THEN 'Genap' ELSE 'Pendek' END as label")
                ->selectRaw('SUM(pm.nominal_bayar) as total')
                ->groupBy('ta.semester')
                ->orderBy('ta.semester')
                ->get(),
            default => $query
                ->selectRaw("DATE_FORMAT(pm.tanggal_bayar, '%Y-%m') as periode_id")
                ->selectRaw("DATE_FORMAT(pm.tanggal_bayar, '%Y-%m') as label")
                ->selectRaw('SUM(pm.nominal_bayar) as total')
                ->groupBy('periode_id', 'label')
                ->orderBy('periode_id')
                ->get(),
        };
    }
}