<?php

namespace App\Services\Pembayaran\Channels;

use App\Models\MidtransTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use RuntimeException;

/**
 * Beda peran dari MahasiswaUploadChannel: channel ini TIDAK langsung
 * mencatat pembayaran (tidak memanggil PembayaranIntakeService). Yang
 * mencatat ke pembayaran_mahasiswas adalah MidtransWebhookController,
 * karena satu-satunya sumber kebenaran soal "sudah dibayar atau belum"
 * untuk payment gateway adalah notifikasi server-to-server dari
 * Midtrans, bukan redirect di browser mahasiswa (yang bisa gagal
 * ditengah jalan, di-refresh, atau dimanipulasi client-side).
 *
 * V1: mahasiswa membayar SISA TAGIHAN PENUH lewat Midtrans (tidak ada
 * pembayaran parsial via gateway). Kalau butuh cicilan via Midtrans,
 * itu perluasan terpisah.
 */
class MidtransChannel
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * @return array{order_id: string, snap_token: string}
     */
    public function initiate(Model $tagihan, string $mahasiswaId, array $customerDetails = []): array
    {
        $sisaTagihan = bcsub((string) $tagihan->total_tagihan, (string) $tagihan->total_bayar, 2);

        if (bccomp($sisaTagihan, '0.00', 2) <= 0) {
            throw new RuntimeException('Tagihan ini sudah lunas, tidak bisa dibuatkan transaksi pembayaran baru.');
        }

        $orderId = 'MT-' . Str::uuid()->toString();

        // Midtrans mewajibkan gross_amount berupa integer (Rupiah tidak
        // punya pecahan dalam praktiknya), tapi cross-check nominal di
        // webhook tetap pakai format desimal 2 digit yang konsisten
        // dengan seluruh sistem (lihat MidtransWebhookController).
        $grossAmount = (int) round((float) $sisaTagihan);

        $snapToken = Snap::getSnapToken([
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => $customerDetails,
            'enabled_payments' => null, // biarkan semua metode yang aktif di akun Midtrans Anda muncul
        ]);

        MidtransTransaction::create([
            'order_id' => $orderId,
            'tagihan_id' => $tagihan->id,
            'tagihan_type' => $tagihan->getMorphClass(),
            'mahasiswa_id' => $mahasiswaId,
            'nominal' => $sisaTagihan,
            'snap_token' => $snapToken,
        ]);

        return [
            'order_id' => $orderId,
            'snap_token' => $snapToken,
        ];
    }
}
