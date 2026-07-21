<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan;

use App\Models\KeuanganGeneralLedger;
use App\Models\LaporanKeuangan\KeuanganGeneralLedgerRecord;
use Illuminate\Database\Eloquent\Builder;

/**
 * Laporan #10 — Laporan Saldo, bersumber dari `keuangan_general_ledgers`.
 *
 * WAJIB difilter per mahasiswa (konsep saldo berjalan hanya relevan
 * untuk satu akun pada satu waktu). Jika mahasiswa_id tidak diisi,
 * query() mengembalikan builder yang dijamin kosong (whereRaw('1 = 0'))
 * — bukan menahan/menge-collect apa pun secara manual, supaya Filament
 * tetap bisa memaginate seperti biasa (hasilnya cuma 0 baris) tanpa kode
 * khusus di trait/Page.
 */
final class SaldoService
{
    public function query(array $filters): Builder
    {
        $mahasiswaId = $filters['mahasiswa_id'] ?? null;

        $query = KeuanganGeneralLedger::query();

        if (blank($mahasiswaId)) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->where('mahasiswa_id', $mahasiswaId)
            ->when($filters['tanggal_dari'] ?? null, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['tanggal_sampai'] ?? null, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderBy('created_at')
            ->selectRaw('
                created_at as tanggal,
                referensi_dokumen,
                tipe_transaksi,
                debit,
                kredit,
                saldo_berjalan,
                keterangan
            ');
    }

    public function saldoAkhir(string $mahasiswaId): float
    {
        $last = KeuanganGeneralLedger::query()
            ->where('mahasiswa_id', $mahasiswaId)
            ->orderByDesc('created_at')
            ->first();

        return (float) ($last->saldo_berjalan ?? 0);
    }
}
