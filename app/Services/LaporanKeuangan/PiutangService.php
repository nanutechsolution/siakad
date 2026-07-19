<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan;

use App\Services\LaporanKeuangan\Support\MahasiswaInfoQuery;
use App\Services\LaporanKeuangan\Support\TagihanMapQuery;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Laporan #3 — Monitoring Piutang.
 *
 * Menampilkan mahasiswa yang masih memiliki sisa tagihan (sisa_tagihan > 0),
 * gabungan tagihan SEMESTER dan NON_REGULER.
 */
final class PiutangService
{
    public function rows(array $filters): Collection
    {
        $map = TagihanMapQuery::build();

        $query = MahasiswaInfoQuery::base()
            ->joinSub($map, 'tm', fn($join) => $join->on('tm.mahasiswa_id', '=', 'm.id'))
            ->where('tm.sisa_tagihan', '>', 0)
            ->when($filters['jenis_tagihan'] ?? null, fn($q, $v) => $q->where('tm.jenis_tagihan', $v));

        $query = MahasiswaInfoQuery::applyFilters($query, $filters);

        $rows = $query
            ->select([
                'm.nim',
                'p.nama_lengkap',
                'pr.nama_prodi',
                'tm.total_tagihan',
                'tm.total_bayar',
                'tm.sisa_tagihan',
                'tm.tenggat_waktu',
            ])
            ->orderBy('tm.tenggat_waktu')
            ->get();

        return $rows->map(function (\stdClass $row): \stdClass {
            $row->hari_keterlambatan = $this->hariKeterlambatan($row->tenggat_waktu);

            return $row;
        });
    }

    /**
     * Ringkasan untuk Summary Card di halaman.
     */
    public function summary(array $filters): array
    {
        $map = TagihanMapQuery::build();

        $query = MahasiswaInfoQuery::base()
            ->joinSub($map, 'tm', fn($join) => $join->on('tm.mahasiswa_id', '=', 'm.id'))
            ->when($filters['jenis_tagihan'] ?? null, fn($q, $v) => $q->where('tm.jenis_tagihan', $v));

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

    private function hariKeterlambatan(?string $tenggatWaktu): int
    {
        if ($tenggatWaktu === null) {
            return 0;
        }

        $tenggat = Carbon::parse($tenggatWaktu)->startOfDay();
        $today = Carbon::today();

        return $tenggat->lessThan($today) ? $tenggat->diffInDays($today) : 0;
    }
}
