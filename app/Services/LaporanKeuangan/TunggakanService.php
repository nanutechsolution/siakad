<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan;

use App\Services\LaporanKeuangan\Support\MahasiswaInfoQuery;
use App\Services\LaporanKeuangan\Support\TagihanMapQuery;
use Illuminate\Database\Eloquent\Builder;

/**
 * Laporan #4 — Rekap Tunggakan (untuk proses penagihan).
 *
 * Kategori tunggakan berdasarkan jumlah hari sejak tenggat_waktu
 * terlewati (dihitung di SQL via CASE, referensi ke
 * `tm.hari_keterlambatan` yang sudah tersedia dari TagihanMapQuery):
 * - RINGAN : 1–30 hari
 * - SEDANG : 31–90 hari
 * - BERAT  : > 90 hari
 *
 * Ambang batas ini adalah keputusan bisnis default (dikonfirmasi
 * bersama pengguna). Ubah konstanta di bawah bila kebijakan penagihan
 * berbeda — otomatis berlaku baik untuk tampilan tabel maupun export,
 * karena keduanya memakai query yang sama.
 */
final class TunggakanService
{
    private const BATAS_RINGAN_MAX_HARI = 30;

    private const BATAS_SEDANG_MAX_HARI = 90;

    public function query(array $filters): Builder
    {
        $map = TagihanMapQuery::build();

        $query = MahasiswaInfoQuery::base()
            ->joinSub($map, 'tm', fn ($join) => $join->on('tm.mahasiswa_id', '=', 'm.id'))
            ->leftJoin('ref_tahun_akademik as ta', 'ta.id', '=', 'tm.tahun_akademik_id')
            ->where('tm.sisa_tagihan', '>', 0)
            ->when($filters['semester'] ?? null, fn ($q, $v) => $q->where('ta.semester', $v))
            ->when($filters['jenis_tagihan'] ?? null, fn ($q, $v) => $q->where('tm.jenis_tagihan', $v));

        $query = MahasiswaInfoQuery::applyFilters($query, $filters);

        return $query
            ->orderByDesc('tm.sisa_tagihan')
            ->selectRaw('
                m.nim,
                p.nama_lengkap,
                pr.nama_prodi,
                ta.semester,
                ta.nama_tahun,
                tm.sisa_tagihan as jumlah_tunggakan,
                tm.tenggat_waktu,
                tm.status_bayar,
                tm.hari_keterlambatan as lama_tunggakan_hari,
                CASE
                    WHEN tm.hari_keterlambatan > '.self::BATAS_SEDANG_MAX_HARI.' THEN \'BERAT\'
                    WHEN tm.hari_keterlambatan > '.self::BATAS_RINGAN_MAX_HARI.' THEN \'SEDANG\'
                    WHEN tm.hari_keterlambatan > 0 THEN \'RINGAN\'
                    ELSE \'BELUM_JATUH_TEMPO\'
                END as kategori_tunggakan
            ');
    }
}
