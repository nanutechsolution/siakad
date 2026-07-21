<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan;

use App\Services\LaporanKeuangan\Support\MahasiswaInfoQuery;
use App\Services\LaporanKeuangan\Support\TagihanMapQuery;
use Illuminate\Database\Eloquent\Builder;

/**
 * Laporan #3 — Monitoring Piutang.
 *
 * `hari_keterlambatan` sudah dihitung di SQL (lihat TagihanMapQuery),
 * jadi query() ini murni SELECT+JOIN — tidak ada post-processing PHP.
 */
final class PiutangService
{
    public function query(array $filters): Builder
    {
        $map = TagihanMapQuery::build();

        $query = MahasiswaInfoQuery::base()
            ->joinSub($map, 'tm', fn ($join) => $join->on('tm.mahasiswa_id', '=', 'm.id'))
            ->where('tm.sisa_tagihan', '>', 0)
            ->when($filters['jenis_tagihan'] ?? null, fn ($q, $v) => $q->where('tm.jenis_tagihan', $v));

        $query = MahasiswaInfoQuery::applyFilters($query, $filters);

        return $query
            ->orderBy('tm.tenggat_waktu')
            ->selectRaw('
                m.nim,
                p.nama_lengkap,
                pr.nama_prodi,
                tm.total_tagihan,
                tm.total_bayar,
                tm.sisa_tagihan,
                tm.tenggat_waktu,
                tm.hari_keterlambatan
            ');
    }

    /**
     * Ringkasan untuk Summary Card. Ini query AGREGAT (SUM/COUNT),
     * hasilnya selalu 1 baris — bukan bagian dari isu performa yang
     * diaudit (bukan "ambil semua baris"), aman dieksekusi langsung.
     */
    public function summary(array $filters): array
    {
        $map = TagihanMapQuery::build();

        $query = MahasiswaInfoQuery::base()
            ->joinSub($map, 'tm', fn ($join) => $join->on('tm.mahasiswa_id', '=', 'm.id'))
            ->when($filters['jenis_tagihan'] ?? null, fn ($q, $v) => $q->where('tm.jenis_tagihan', $v));

        $query = MahasiswaInfoQuery::applyFilters($query, $filters);

        $agg = (clone $query)
            ->selectRaw('SUM(tm.total_tagihan) as total_tagihan')
            ->selectRaw('SUM(tm.total_bayar) as total_pembayaran')
            ->selectRaw('SUM(tm.sisa_tagihan) as total_piutang')
            ->first();

        $jumlahMenunggak = (clone $query)
            ->where('tm.sisa_tagihan', '>', 0)
            ->select('m.id')
            ->distinct()
            ->count();

        return [
            'total_tagihan' => (float) ($agg->total_tagihan ?? 0),
            'total_pembayaran' => (float) ($agg->total_pembayaran ?? 0),
            'total_piutang' => (float) ($agg->total_piutang ?? 0),
            'jumlah_mahasiswa_menunggak' => (int) $jumlahMenunggak,
        ];
    }
}
