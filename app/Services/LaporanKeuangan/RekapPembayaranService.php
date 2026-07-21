<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan;

use App\Models\LaporanKeuangan\PembayaranMahasiswaRecord;
use App\Services\LaporanKeuangan\Support\MahasiswaInfoQuery;
use App\Services\LaporanKeuangan\Support\TagihanMapQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Laporan #2 — Rekap Pembayaran.
 */
final class RekapPembayaranService
{
    public function query(array $filters): Builder
    {
        $map = TagihanMapQuery::build();

        $query = MahasiswaInfoQuery::base()
            ->joinSub($map, 'tm', fn($join) => $join->on('tm.mahasiswa_id', '=', 'mahasiswas.id'))
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
            ->orderByDesc('pm.tanggal_bayar')
            ->selectRaw('
                mahasiswas.id,
                pm.id as nomor_transaksi,
                pm.tanggal_bayar,
                mahasiswas.nim,
                mahasiswas.id,
                p.nama_lengkap,
                pr.nama_prodi,
                tm.jenis_tagihan,
                pm.nominal_bayar,
                pm.metode_pembayaran,
                sv.nama as status_verifikasi,
                u.name as user_verifikasi
            ');
    }

    /**
     * Daftar metode pembayaran unik yang benar-benar ada di database,
     * dipakai untuk mengisi opsi filter (kolom ini bukan tabel referensi).
     * Dataset kecil (jumlah metode pembayaran terbatas) — aman di-->get().
     */
    public function distinctMetodePembayaran(): Collection
    {
        return PembayaranMahasiswaRecord::query()
            ->whereNull('deleted_at')
            ->distinct()
            ->orderBy('metode_pembayaran')
            ->pluck('metode_pembayaran');
    }
}
