<?php

namespace App\Http\Controllers;

use App\DTOs\Pembayaran\PembayaranIntakeData;
use App\Enums\MetodePembayaran;
use App\Exceptions\Pembayaran\PembayaranSudahDiprosesException;
use App\Models\MidtransGatewayLog;
use App\Models\MidtransTransaction;
use App\Services\Pembayaran\PembayaranVerificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\Pembayaran;
class MidtransWebhookController extends Controller
{
    private const STATUS_SUKSES = ['settlement', 'capture'];
    private const STATUS_GAGAL = ['deny', 'cancel', 'expire'];

    public function __construct(
        private readonly PembayaranIntakeService $intakeService,
        private readonly PembayaranVerificationService $verificationService,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        Log::info('MIDTRANS CALLBACK MASUK', [
            'payload' => $request->all()
        ]);

        $payload = $request->all();

        // 1. Log MENTAH dulu, sebelum validasi apa pun — supaya kalaupun
        // langkah berikutnya gagal/ditolak, kita tetap punya jejaknya.
        MidtransGatewayLog::create([
            'order_id' => $payload['order_id'] ?? null,
            'transaction_status' => $payload['transaction_status'] ?? null,
            'payload' => $payload,
        ]);

        // 2. Signature WAJIB valid sebelum payload dipercaya sama sekali.
        if (! $this->signatureValid($payload)) {
            Log::warning('Midtrans webhook: signature tidak valid', ['order_id' => $payload['order_id'] ?? null]);

            return response()->json(['status' => 'invalid_signature'], 403);
        }

        $trx = MidtransTransaction::where('order_id', $payload['order_id'] ?? null)->first();

        if (! $trx) {
            // Bukan berarti serangan — bisa juga webhook untuk order_id
            // dari environment lain (sandbox/production ketuker). Log
            // saja, jangan proses.
            Log::warning('Midtrans webhook: order_id tidak dikenal', ['order_id' => $payload['order_id'] ?? null]);

            return response()->json(['status' => 'ignored']);
        }

        // 3. Nominal WAJIB cocok dengan yang kita minta saat initiate —
        // pertahanan kalau ada yang coba modifikasi gross_amount.
        if (! $this->nominalValid($trx, $payload)) {
            Log::critical('Midtrans webhook: nominal TIDAK COCOK dengan yang diminta saat initiate', [
                'order_id' => $trx->order_id,
                'nominal_diminta' => $trx->nominal,
                'gross_amount_diterima' => $payload['gross_amount'] ?? null,
            ]);

            return response()->json(['status' => 'nominal_mismatch'], 422);
        }

        $status = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;

        // Kartu kredit: capture dengan fraud_status selain 'accept' berarti
        // ditahan sistem fraud detection Midtrans — JANGAN diverifikasi.
        if ($status === 'capture' && $fraudStatus !== null && $fraudStatus !== 'accept') {
            Log::warning('Midtrans webhook: capture ditahan fraud detection', ['order_id' => $trx->order_id, 'fraud_status' => $fraudStatus]);

            return response()->json(['status' => 'held_for_fraud_review']);
        }

        // 4. Catat/pastikan ada baris pembayaran (idempotent lewat
        // idempotency_key = order_id — webhook yang direplay Midtrans
        // tidak akan bikin baris dobel).
        $pembayaran = $this->intakeService->catat(PembayaranIntakeData::make(
            tagihanId: $trx->tagihan_id,
            tagihanType: $trx->tagihan_type,
            nominalBayar: (string) ($payload['gross_amount'] ?? $trx->nominal),
            tanggalBayar: isset($payload['transaction_time']) ? Carbon::parse($payload['transaction_time']) : now(),
            metodePembayaran: MetodePembayaran::MIDTRANS,
            idempotencyKey: $trx->order_id,
            buktiBayarPath: null,
            keteranganPengirim: 'Midtrans - ' . ($payload['payment_type'] ?? '-'),
        ));

        // 5. Sesuaikan status berdasarkan transaction_status Midtrans.
        if (in_array($status, self::STATUS_SUKSES, true)) {
            try {
                $this->verificationService->verifikasi($pembayaran, config('midtrans.system_verifier_user_id'));
            } catch (PembayaranSudahDiprosesException) {
                // Webhook direplay setelah status sebelumnya sudah final —
                // aman diabaikan, bukan error.
            }
        } elseif (in_array($status, self::STATUS_GAGAL, true)) {
            try {
                $this->verificationService->tolak(
                    $pembayaran->id,
                    config('midtrans.system_verifier_user_id'),
                    "Ditolak/dibatalkan oleh Midtrans (status: {$status})",
                );
            } catch (PembayaranSudahDiprosesException) {
                //
            }
        }
        // status 'pending' -> sengaja tidak ada aksi lanjutan, baris
        // pembayaran sudah tercatat PENDING dari langkah 4 di atas,
        // menunggu webhook status final berikutnya.

        return response()->json(['status' => 'ok']);
    }

    private function signatureValid(array $payload): bool
    {
        if (! isset($payload['order_id'], $payload['status_code'], $payload['gross_amount'], $payload['signature_key'])) {
            return false;
        }

        $signature = hash(
            'sha512',
            $payload['order_id'] .
                $payload['status_code'] .
                $payload['gross_amount'] .
                config('midtrans.server_key')
        );

        return hash_equals($signature, $payload['signature_key']);
    }

    private function nominalValid(MidtransTransaction $trx, array $payload): bool
    {
        $diminta = number_format((float) $trx->nominal, 2, '.', '');
        $diterima = number_format((float) ($payload['gross_amount'] ?? 0), 2, '.', '');

        return hash_equals($diminta, $diterima);
    }
}
