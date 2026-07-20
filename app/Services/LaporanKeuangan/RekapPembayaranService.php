<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan;

use App\Services\LaporanKeuangan\Support\MahasiswaInfoQuery;
use App\Services\LaporanKeuangan\Support\TagihanMapQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Laporan #2 — Rekap Pembayaran.
 *
 * Filter yang didukung (semua opsional):
 * - status_verifikasi_id
 * - metode_pembayaran
 * - tanggal_dari, tanggal_sampai (rentang tanggal_bayar)
 * - fakultas_id, prodi_id, angkatan_id
 */
final class RekapPembayaranService
{
    public function rows(array $filters): Collection
    {
        $map = TagihanMapQuery::build();

        $query = MahasiswaInfoQuery::base()
            ->joinSub($map, 'tm', fn($join) => $join->on('tm.mahasiswa_id', '=', 'm.id'))
            ->join('pembayaran_mahasiswas as pm', 'pm.tagihan_id', '=', 'tm.tagihan_id')
            ->join('ref_status_verifikasi_pembayaran as sv', 'sv.id', '=', 'pm.status_verifikasi_id')
            ->leftJoin('users as u', 'u.id', '=', 'pm.verified_by')
            ->whereNull('pm.deleted_at')
            ->when($filters['status_verifikasi_id'] ?? null, fn($q, $v) => $q->where('pm.status_verifikasi_id', $v))
            ->when($filters['metode_pembayaran'] ?? null, fn($q, $v) => $q->where('pm.metode_pembayaran', $v))
            ->when($filters['tanggal_dari'] ?? null, fn($q, $v) => $q->whereDate('pm.tanggal_bayar', '>=', $v))
            ->when($filters['tanggal_sampai'] ?? null, fn($q, $v) => $q->whereDate('pm.tanggal_bayar', '<=', $v));

        $query = MahasiswaInfoQuery::applyFilters($query, $filters);

        return $query
            ->select([
                'pm.id as nomor_transaksi',
                'pm.tanggal_bayar',
                'm.nim',
                'p.nama_lengkap',
                'pr.nama_prodi',
                'tm.jenis_tagihan',
                'pm.nominal_bayar',
                'pm.metode_pembayaran',
                'sv.nama as status_verifikasi',
                'u.name as user_verifikasi',
            ])
            ->orderByDesc('pm.tanggal_bayar')
            ->get();
    }

    /**
     * Daftar metode pembayaran unik yang benar-benar ada di database,
     * dipakai untuk mengisi opsi filter (kolom ini bukan tabel referensi).
     */
    public function distinctMetodePembayaran(): Collection
    {
        return DB::table('pembayaran_mahasiswas')
            ->whereNull('deleted_at')
            ->distinct()
            ->orderBy('metode_pembayaran')
            ->pluck('metode_pembayaran');
    }
}
