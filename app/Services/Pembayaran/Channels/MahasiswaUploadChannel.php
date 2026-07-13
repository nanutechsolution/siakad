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

    public function process(array $payload): PembayaranMahasiswa
    {
        // $payload berasal dari array $data yang dihasilkan oleh Filament Form (Action)
        $dto = PembayaranIntakeData::make(
            tagihanId: $payload['tagihan_id'],
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
