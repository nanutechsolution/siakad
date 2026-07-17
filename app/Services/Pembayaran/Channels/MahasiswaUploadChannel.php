<?php

namespace App\Services\Pembayaran\Channels;

use App\DTOs\Pembayaran\PembayaranIntakeData;
use App\Enums\MetodePembayaran;
use App\Models\PembayaranMahasiswa;
use App\Services\Pembayaran\PembayaranIntakeService;
use Carbon\Carbon;

class MahasiswaUploadChannel implements PaymentChannelInterface
{
    public function __construct(
        private readonly PembayaranIntakeService $intakeService
    ) {}

    /**
     * $payload berasal dari array $data yang dihasilkan oleh Filament
     * Form (Action). Sekarang WAJIB menyertakan 'tagihan_type' (morph
     * alias, mis. 'tagihan_mahasiswa' atau 'tagihan_non_reguler') supaya
     * channel ini tetap satu pintu untuk kedua jenis tagihan — tanpa ini
     * PembayaranIntakeService::pastikanTagihanValid() akan menolak.
     */
    public function process(array $payload): PembayaranMahasiswa
    {
        $dto = PembayaranIntakeData::make(
            tagihanId: $payload['tagihan_id'],
            tagihanType: $payload['tagihan_type'],
            nominalBayar: $payload['nominal_bayar'],
            tanggalBayar: Carbon::parse($payload['tanggal_bayar']),
            metodePembayaran: MetodePembayaran::MANUAL, // Khusus Mahasiswa Upload
            idempotencyKey: null, // Karena upload manual, biarkan DTO yang men-generate ID unik (UUID)
            buktiBayarPath: $payload['bukti_bayar_path'] ?? null,
            keteranganPengirim: $payload['keterangan_pengirim'] ?? 'Diunggah mandiri oleh Mahasiswa'
        );

        return $this->intakeService->catat($dto);
    }
}