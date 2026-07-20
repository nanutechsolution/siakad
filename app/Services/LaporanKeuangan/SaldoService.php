<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Laporan #10 — Laporan Saldo, bersumber dari `keuangan_general_ledgers`
 * (kolom debit/kredit/saldo_berjalan per mahasiswa sudah sesuai kebutuhan
 * laporan tanpa perlu agregasi tambahan).
 *
 * Laporan ini WAJIB difilter per mahasiswa (konsep saldo berjalan hanya
 * relevan untuk satu akun/mahasiswa pada satu waktu). Jika mahasiswa_id
 * tidak diisi, method rows() mengembalikan collection kosong.
 */
final class SaldoService
{
    public function rows(array $filters): Collection
    {
        $mahasiswaId = $filters['mahasiswa_id'] ?? null;

        if (blank($mahasiswaId)) {
            return collect();
        }

        return DB::table('keuangan_general_ledgers')
            ->where('mahasiswa_id', $mahasiswaId)
            ->when($filters['tanggal_dari'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['tanggal_sampai'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->select([
                'created_at as tanggal',
                'referensi_dokumen',
                'tipe_transaksi',
                'debit',
                'kredit',
                'saldo_berjalan',
                'keterangan',
            ])
            ->orderBy('created_at')
            ->get();
    }

    public function saldoAkhir(string $mahasiswaId): float
    {
        $last = DB::table('keuangan_general_ledgers')
            ->where('mahasiswa_id', $mahasiswaId)
            ->orderByDesc('created_at')
            ->first();

        return (float) ($last->saldo_berjalan ?? 0);
    }
}