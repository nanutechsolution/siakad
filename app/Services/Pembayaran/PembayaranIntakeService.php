<?php

namespace App\Services\Pembayaran;

use App\DTOs\Pembayaran\PembayaranIntakeData;
use App\Enums\StatusVerifikasiPembayaran;
use App\Events\PembayaranDiterima;
use App\Exceptions\Pembayaran\NominalPembayaranTidakValidException;
use App\Exceptions\Pembayaran\TagihanTidakDitemukanException;
use App\Models\PembayaranMahasiswa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class PembayaranIntakeService
{
    /**
     * Mencatat klaim pembayaran dari channel manapun (Admin, Mahasiswa,
     * VA, QRIS, Midtrans, Xendit, Import Bank).
     *
     * SECARA SENGAJA TIDAK MELAKUKAN:
     * - Menghitung sisa tagihan
     * - Mengubah tagihan_mahasiswas / tagihan_non_regulers
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

        $this->pastikanTagihanValid($data->tagihanType, $data->tagihanId);

        $existing = PembayaranMahasiswa::where('idempotency_key', $data->idempotencyKey)->first();
        if ($existing) {
            // Replay webhook / double submit — kembalikan record yang sudah ada, jangan duplikasi.
            return $existing;
        }

        $pembayaran = PembayaranMahasiswa::create([
            'idempotency_key' => $data->idempotencyKey,
            'tagihan_id' => $data->tagihanId,
            'tagihan_type' => $data->tagihanType,
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

    /**
     * Pengganti FK constraint yang sudah dilepas dari kolom tagihan_id
     * (lihat migration make_pembayaran_mahasiswas_tagihan_polymorphic).
     * Karena tagihan_id sekarang polymorphic, integritas referensial
     * WAJIB divalidasi di sini secara eksplisit — tidak ada lagi jaring
     * pengaman dari database.
     */
    private function pastikanTagihanValid(string $tagihanType, string $tagihanId): void
    {
        $modelClass = Relation::getMorphedModel($tagihanType);

        if ($modelClass === null || ! is_subclass_of($modelClass, Model::class)) {
            throw TagihanTidakDitemukanException::tipeTidakDikenal($tagihanType);
        }

        if (! $modelClass::query()->whereKey($tagihanId)->exists()) {
            throw TagihanTidakDitemukanException::tidakDitemukan($tagihanType, $tagihanId);
        }
    }
}