<?php

namespace App\Services\Pembayaran\Channels;

use App\DTOs\Pembayaran\PembayaranIntakeData;
use App\Enums\MetodePembayaran;
use App\Models\PembayaranMahasiswa;
use App\Services\Pembayaran\PembayaranIntakeService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AdminManualChannel implements PaymentChannelInterface
{
    public function __construct(
        private readonly PembayaranIntakeService $intakeService
    ) {}

    public function process(array $payload): PembayaranMahasiswa
    {
        // Menambahkan nama Admin ke keterangan agar log-nya lebih jelas
        $adminName = Auth::check() ? Auth::user()->name : 'Sistem';
        $keterangan = $payload['keterangan_pengirim'] ?? "Diinput manual oleh Admin/Staf ({$adminName})";

        $dto = PembayaranIntakeData::make(
            tagihanId: $payload['tagihan_id'],
            nominalBayar: $payload['nominal_bayar'],
            tanggalBayar: Carbon::parse($payload['tanggal_bayar']),
            metodePembayaran: MetodePembayaran::ADMIN, // Khusus input Admin
            idempotencyKey: null, // Biarkan DTO generate ID
            buktiBayarPath: $payload['bukti_bayar_path'] ?? null,
            keteranganPengirim: $keterangan
        );

        return $this->intakeService->catat($dto);
    }
}