<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan;

use App\Services\LaporanKeuangan\Support\MahasiswaInfoQuery;
use App\Services\LaporanKeuangan\Support\TagihanMapQuery;
use Illuminate\Database\Eloquent\Builder;

/**
 * Laporan #9 — Rekap Cicilan.
 *
 * PERFORMA: versi sebelumnya menghitung "jumlah cicilan" via query
 * batch terpisah setelah ->get() (untuk menghindari N+1 per baris).
 * Sekarang dipindah jadi correlated subquery di SELECT — jalan sama
 * baiknya untuk tabel yang dipaginate (hanya dihitung untuk baris pada
 * halaman aktif) MAUPUN saat export di-chunk (dihitung per baris dalam
 * chunk, tanpa query batch terpisah lagi karena sudah menyatu di SELECT
 * yang sama, jadi tetap 1 query per halaman/chunk, bukan N+1).
 *
 * CATATAN: Schema tidak memiliki tabel jadwal cicilan formal. "Jumlah
 * Cicilan" adalah nilai turunan = jumlah baris pembayaran terverifikasi
 * FINAL pada tagihan tersebut — bukan data eksplisit sistem cicilan.
 */
final class CicilanService
{
    public function query(array $filters): Builder
    {
        $map = TagihanMapQuery::build();

        $query = MahasiswaInfoQuery::base()
            ->joinSub($map, 'tm', fn ($join) => $join->on('tm.mahasiswa_id', '=', 'm.id'))
            ->where('tm.status_bayar', 'CICIL')
            ->when($filters['jenis_tagihan'] ?? null, fn ($q, $v) => $q->where('tm.jenis_tagihan', $v));

        $query = MahasiswaInfoQuery::applyFilters($query, $filters);

        return $query
            ->orderBy('p.nama_lengkap')
            ->selectRaw('
                m.nim,
                p.nama_lengkap,
                pr.nama_prodi,
                tm.total_tagihan,
                tm.total_bayar as sudah_dibayar,
                tm.sisa_tagihan,
                tm.status_bayar,
                (
                    SELECT COUNT(*) FROM pembayaran_mahasiswas pm2
                    JOIN ref_status_verifikasi_pembayaran sv2 ON sv2.id = pm2.status_verifikasi_id
                    WHERE pm2.tagihan_id = tm.tagihan_id
                      AND sv2.is_final = 1
                      AND pm2.deleted_at IS NULL
                ) as jumlah_cicilan
            ');
    }
}
