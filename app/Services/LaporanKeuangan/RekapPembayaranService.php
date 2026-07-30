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
        // 1. Mulai dari model Pembayaran, BUKAN Mahasiswa
        return PembayaranMahasiswaRecord::query()
            ->from('pembayaran_mahasiswas as pm')

            // 2. Join ke pemetaan tagihan
            ->joinSub(TagihanMapQuery::build(), 'tm', fn($join) => $join->on('tm.tagihan_id', '=', 'pm.tagihan_id'))

            // 3. Join ke tabel mahasiswa dan relasi pendukungnya
            ->join('mahasiswas as m', 'm.id', '=', 'tm.mahasiswa_id')
            ->join('persons as p', 'p.id', '=', 'm.person_id')
            ->join('ref_program_studis as pr', 'pr.id', '=', 'm.prodi_id')

            // 4. Join status dan user
            ->join('ref_status_verifikasi_pembayaran as sv', 'sv.id', '=', 'pm.status_verifikasi_id')
            ->leftJoin('users as u', 'u.id', '=', 'pm.verified_by')

            ->whereNull('pm.deleted_at')

            // 5. Terapkan filter Pembayaran
            ->when($filters['status_verifikasi_id'] ?? null, fn($q, $v) => $q->where('pm.status_verifikasi_id', $v))
            ->when($filters['metode_pembayaran'] ?? null, fn($q, $v) => $q->where('pm.metode_pembayaran', $v))
            ->when($filters['tanggal_dari'] ?? null, fn($q, $v) => $q->whereDate('pm.tanggal_bayar', '>=', $v))
            ->when($filters['tanggal_sampai'] ?? null, fn($q, $v) => $q->whereDate('pm.tanggal_bayar', '<=', $v))

            // 6. Terapkan filter Mahasiswa (Fakultas & Prodi)
            ->when($filters['prodi_id'] ?? null, fn($q, $v) => $q->where('m.prodi_id', $v))
            ->when($filters['fakultas_id'] ?? null, fn($q, $v) => $q->where('pr.fakultas_id', $v))

            // 7. Pilih kolom secara spesifik
            ->select([
                'pm.id', // WAJIB ADA untuk mencegah Livewire DOM Error
                'pm.id as nomor_transaksi',
                'pm.tanggal_bayar',
                'm.nim',
                'p.nama_lengkap',
                'pr.nama_prodi',
                'tm.jenis_tagihan',
                'pm.nominal_bayar',
                'pm.metode_pembayaran',
                'sv.nama as status_verifikasi',
                'u.name as user_verifikasi'
            ])

            // 8. Kunci dengan Group By untuk menjamin tidak ada duplikasi mutlak
            ->groupBy([
                'pm.id',
                'pm.tanggal_bayar',
                'm.nim',
                'p.nama_lengkap',
                'pr.nama_prodi',
                'tm.jenis_tagihan',
                'pm.nominal_bayar',
                'pm.metode_pembayaran',
                'sv.nama',
                'u.name'
            ])
            ->orderByDesc('pm.tanggal_bayar');
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
