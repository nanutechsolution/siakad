<?php

namespace App\Services\Pembayaran;

use App\Enums\StatusVerifikasiPembayaran;
use App\Events\PembayaranTerverifikasi;
use App\Exceptions\Pembayaran\PembayaranSudahDiprosesException;
use App\Models\PembayaranMahasiswa;
use Illuminate\Support\Facades\DB;

class PembayaranVerificationService
{
    public function __construct(
        private readonly PembayaranAllocationService $allocationService,
    ) {}

    public function verifikasi(string $pembayaranId, string $verifikatorUserId): PembayaranMahasiswa
    {
        return DB::transaction(function () use ($pembayaranId, $verifikatorUserId) {
            $pembayaran = PembayaranMahasiswa::whereKey($pembayaranId)
                ->lockForUpdate()
                ->firstOrFail();

            $this->pastikanMasihPending($pembayaran);

            $pembayaran->status_verifikasi_id = StatusVerifikasiPembayaran::VERIFIED;
            $pembayaran->verified_by = $verifikatorUserId;
            $pembayaran->verified_at = now();
            $pembayaran->save();

            $this->allocationService->alokasikan($pembayaran);

            $pembayaran->refresh();

            event(new PembayaranTerverifikasi($pembayaran));

            return $pembayaran;
        });
    }

    public function tolak(string $pembayaranId, string $verifikatorUserId, string $catatan): PembayaranMahasiswa
    {
        return DB::transaction(function () use ($pembayaranId, $verifikatorUserId, $catatan) {
            $pembayaran = PembayaranMahasiswa::whereKey($pembayaranId)
                ->lockForUpdate()
                ->firstOrFail();

            $this->pastikanMasihPending($pembayaran);

            $pembayaran->status_verifikasi_id = StatusVerifikasiPembayaran::REJECTED;
            $pembayaran->verified_by = $verifikatorUserId;
            $pembayaran->verified_at = now();
            $pembayaran->catatan_verifikasi = $catatan;
            $pembayaran->save();

            return $pembayaran;
        });
    }

    private function pastikanMasihPending(PembayaranMahasiswa $pembayaran): void
    {
        if ($pembayaran->status_verifikasi_id !== StatusVerifikasiPembayaran::PENDING) {
            throw new PembayaranSudahDiprosesException($pembayaran);
        }
    }
}
