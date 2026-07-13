<?php

namespace App\Services\Pembayaran\Channels;

use App\Models\PembayaranMahasiswa;

interface PaymentChannelInterface
{
    /**
     * Memproses data mentah (dari Filament form state atau Webhook payload)
     * menjadi model PembayaranMahasiswa melalui Intake Service.
     */
    public function process(array $payload): PembayaranMahasiswa;
}
