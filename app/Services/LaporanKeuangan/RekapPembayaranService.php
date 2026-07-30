<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan;

use App\Models\PembayaranMahasiswa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class RekapPembayaranService
{
    public function query(array $filters): Builder
    {
        return PembayaranMahasiswa::query()
            ->with([
                'statusVerifikasi',
                'verifier',
                'tagihan'
            ])
            ->when($filters['status_verifikasi_id'] ?? null, fn($q, $v) => $q->where('status_verifikasi_id', $v))
            ->when($filters['metode_pembayaran'] ?? null, fn($q, $v) => $q->where('metode_pembayaran', $v))
            ->when($filters['tanggal_dari'] ?? null, fn($q, $v) => $q->whereDate('tanggal_bayar', '>=', $v))
            ->when($filters['tanggal_sampai'] ?? null, fn($q, $v) => $q->whereDate('tanggal_bayar', '<=', $v))
            ->when($filters['prodi_id'] ?? null, function ($q, $prodiId) {
                $q->whereHasMorph('tagihan', '*', function ($subQuery) use ($prodiId) {
                    $subQuery->whereHas('mahasiswa', fn($m) => $m->where('prodi_id', $prodiId));
                });
            })
            ->when($filters['fakultas_id'] ?? null, function ($q, $fakultasId) {
                $q->whereHasMorph('tagihan', '*', function ($subQuery) use ($fakultasId) {
                    $subQuery->whereHas('mahasiswa.prodi', fn($p) => $p->where('fakultas_id', $fakultasId));
                });
            })
            ->orderByDesc('tanggal_bayar');
    }

    public function distinctMetodePembayaran(): Collection
    {
        return PembayaranMahasiswa::query()
            ->whereNull('deleted_at')
            ->distinct()
            ->orderBy('metode_pembayaran')
            ->pluck('metode_pembayaran');
    }
}
