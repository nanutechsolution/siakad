<?php

namespace App\Services\Pembayaran;

use App\DTOs\Pembayaran\PembayaranIntakeData;
use App\Enums\StatusVerifikasiPembayaran;
use App\Events\PembayaranDiterima;
use App\Exceptions\Pembayaran\NominalPembayaranTidakValidException;
use App\Models\PembayaranMahasiswa;

class PembayaranIntakeService
{
    /**
     * Mencatat klaim pembayaran dari channel manapun (Admin, Mahasiswa,
     * VA, QRIS, Midtrans, Xendit, Import Bank).
     *
     * SECARA SENGAJA TIDAK MELAKUKAN:
     * - Menghitung sisa tagihan
     * - Mengubah tagihan_mahasiswas
     * - Mengalokasikan ke saldo
     *
     * Pembayaran SELALU masuk dengan status PENDING. Alokasi hanya
     * terjadi lewat PembayaranVerificationService::verifikasi().
     */
    public function catat(PembayaranIntakeData $data): PembayaranMahasiswa
    {
        if (bccomp($data->nominalBayar, '0.00', 2) <= 0) {
            throw NominalPembayaranTidakValidException::harusPositif($data->nominalBayar);
        }

        $existing = PembayaranMahasiswa::where('idempotency_key', $data->idempotencyKey)->first();
        if ($existing) {
            // Replay webhook / double submit — kembalikan record yang sudah ada, jangan duplikasi.
            return $existing;
        }

        $pembayaran = PembayaranMahasiswa::create([
            'idempotency_key' => $data->idempotencyKey,
            'tagihan_id' => $data->tagihanId,
            'nominal_bayar' => $data->nominalBayar,
            'tanggal_bayar' => $data->tanggalBayar,
            'metode_pembayaran' => $data->metodePembayaran,
            'bukti_bayar_path' => $data->buktiBayarPath,
            'keterangan_pengirim' => $data->keteranganPengirim,
            'status_verifikasi_id' => StatusVerifikasiPembayaran::PENDING,
        ]);

        event(new PembayaranDiterima($pembayaran));

        return $pembayaran;
    }
}