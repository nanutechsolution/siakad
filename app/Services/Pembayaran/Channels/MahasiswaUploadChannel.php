<?php

namespace App\Services\Pembayaran\Channels;

use App\DTOs\Pembayaran\PembayaranIntakeData;
use App\Enums\MetodePembayaran;
use App\Enums\StatusVerifikasiPembayaran;
use App\Models\PembayaranMahasiswa;
use App\Services\Pembayaran\PembayaranIntakeService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MahasiswaUploadChannel implements PaymentChannelInterface
{
    public function __construct(
        private readonly PembayaranIntakeService $intakeService
    ) {}

    public function process(array $payload): PembayaranMahasiswa
    {
        return DB::transaction(function () use ($payload) {

            /*
            |--------------------------------------------------------------------------
            | Resolve tipe tagihan
            |--------------------------------------------------------------------------
            */

            $modelClass = Relation::getMorphedModel(
                $payload['tagihan_type']
            );

            if (! $modelClass) {
                throw new Exception(
                    "Tipe tagihan tidak dikenali sistem: {$payload['tagihan_type']}"
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Lock tagihan
            |--------------------------------------------------------------------------
            */

            $tagihan = $modelClass::lockForUpdate()
                ->findOrFail($payload['tagihan_id']);

            /*
            |--------------------------------------------------------------------------
            | Tagihan sudah lunas
            |--------------------------------------------------------------------------
            */

            if ($tagihan->status_bayar === 'LUNAS') {
                throw new Exception(
                    "Tagihan ini sudah berstatus LUNAS. Pembayaran tidak dapat diproses."
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Cegah upload kedua ketika masih PENDING
            |--------------------------------------------------------------------------
            */

            $adaPending = PembayaranMahasiswa::query()
                ->where('tagihan_id', $tagihan->id)
                ->where('tagihan_type', $payload['tagihan_type'])
                ->where(
                    'status_verifikasi_id',
                    StatusVerifikasiPembayaran::PENDING
                )
                ->where(
                    'metode_pembayaran',
                    MetodePembayaran::MANUAL
                )
                ->exists();

            if ($adaPending) {
                throw new Exception(
                    "Anda masih memiliki bukti transfer yang sedang diverifikasi."
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Hitung SHA-256 bukti pembayaran
            |--------------------------------------------------------------------------
            */

            $fileHash = null;

            if (! empty($payload['bukti_bayar_path'])) {

                $disk = Storage::disk('public');

                $path = $payload['bukti_bayar_path'];

                /*
                 * Pastikan file benar-benar ada.
                 */
                if (! $disk->exists($path)) {
                    throw new Exception(
                        "File bukti pembayaran tidak ditemukan. Silakan unggah ulang."
                    );
                }

                $fullPath = $disk->path($path);

                /*
                 * SHA-256 menghasilkan hash 64 karakter.
                 */
                $fileHash = hash_file('sha256', $fullPath);

                if ($fileHash === false) {
                    throw new Exception(
                        "Bukti pembayaran tidak dapat diproses. Silakan unggah ulang."
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Cek apakah bukti yang sama sudah pernah VERIFIED
                |--------------------------------------------------------------------------
                |
                | REJECTED tidak diblokir.
                | PENDING sudah diblokir oleh pengecekan sebelumnya.
                | VERIFIED + hash sama = ditolak.
                |
                */

                $sudahTerverifikasi = PembayaranMahasiswa::query()
                    ->where('tagihan_type', $payload['tagihan_type'])
                    ->where('file_hash', $fileHash)
                    ->where(
                        'status_verifikasi_id',
                        StatusVerifikasiPembayaran::VERIFIED
                    )
                    ->exists();

                if ($sudahTerverifikasi) {
                    throw new Exception(
                        "Bukti pembayaran ini sudah pernah diverifikasi. " .
                            "Jika Anda melakukan pembayaran baru, silakan unggah bukti transfer yang baru."
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Buat DTO
            |--------------------------------------------------------------------------
            */

            $dto = PembayaranIntakeData::make(
                tagihanId: $payload['tagihan_id'],
                tagihanType: $payload['tagihan_type'],
                nominalBayar: $payload['nominal_bayar'],
                tanggalBayar: Carbon::parse($payload['tanggal_bayar']),
                metodePembayaran: MetodePembayaran::MANUAL,
                idempotencyKey: null,
                buktiBayarPath: $payload['bukti_bayar_path'] ?? null,
                bankKampusId: $payload['bank_tujuan_id'] ?? null,
                keteranganPengirim: $payload['catatan']
                    ?? 'Diunggah mandiri oleh Mahasiswa',
                fileHash: $fileHash,
            );

            /*
            |--------------------------------------------------------------------------
            | Simpan pembayaran
            |--------------------------------------------------------------------------
            */

            return $this->intakeService->catat($dto);
        });
    }
}
