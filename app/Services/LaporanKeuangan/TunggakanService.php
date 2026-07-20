<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan;

use App\Services\LaporanKeuangan\Support\MahasiswaInfoQuery;
use App\Services\LaporanKeuangan\Support\TagihanMapQuery;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Laporan #4 — Rekap Tunggakan (untuk proses penagihan).
 *
 * Kategori tunggakan berdasarkan jumlah hari sejak tenggat_waktu terlewati:
 * - RINGAN : 1–30 hari
 * - SEDANG : 31–90 hari
 * - BERAT  : > 90 hari
 *
 * Ambang batas ini adalah keputusan bisnis default (dikonfirmasi bersama
 * pengguna). Ubah konstanta di bawah bila kebijakan penagihan berbeda.
 */
final class TunggakanService
{
    private const BATAS_RINGAN_MAX_HARI = 30;

    private const BATAS_SEDANG_MAX_HARI = 90;

    public function rows(array $filters): Collection
    {
        $map = TagihanMapQuery::build();

        $query = MahasiswaInfoQuery::base()
            ->joinSub($map, 'tm', fn($join) => $join->on('tm.mahasiswa_id', '=', 'm.id'))
            ->leftJoin('ref_tahun_akademik as ta', 'ta.id', '=', 'tm.tahun_akademik_id')
            ->where('tm.sisa_tagihan', '>', 0)
            ->when($filters['semester'] ?? null, fn($q, $v) => $q->where('ta.semester', $v))
            ->when($filters['jenis_tagihan'] ?? null, fn($q, $v) => $q->where('tm.jenis_tagihan', $v));

        $query = MahasiswaInfoQuery::applyFilters($query, $filters);

        $rows = $query
            ->select([
                'm.nim',
                'p.nama_lengkap',
                'pr.nama_prodi',
                'ta.semester',
                'ta.nama_tahun',
                'tm.sisa_tagihan as jumlah_tunggakan',
                'tm.tenggat_waktu',
                'tm.status_bayar',
            ])
            ->orderByDesc('tm.sisa_tagihan')
            ->get();

        return $rows->map(function (\stdClass $row): \stdClass {
            $lamaHari = $this->lamaTunggakanHari($row->tenggat_waktu);
            $row->lama_tunggakan_hari = $lamaHari;
            $row->kategori_tunggakan = $this->kategori($lamaHari);

            return $row;
        });
    }

    public function kategori(int $lamaHari): string
    {
        return match (true) {
            $lamaHari > self::BATAS_SEDANG_MAX_HARI => 'BERAT',
            $lamaHari > self::BATAS_RINGAN_MAX_HARI => 'SEDANG',
            $lamaHari > 0 => 'RINGAN',
            default => 'BELUM_JATUH_TEMPO',
        };
    }

    private function lamaTunggakanHari(?string $tenggatWaktu): int
    {
        if ($tenggatWaktu === null) {
            return 0;
        }

        $tenggat = Carbon::parse($tenggatWaktu)->startOfDay();
        $today = Carbon::today();

        return $tenggat->lessThan($today) ? $tenggat->diffInDays($today) : 0;
    }
}
