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

class MahasiswaUploadChannel implements PaymentChannelInterface
{
    public function __construct(
        private readonly PembayaranIntakeService $intakeService
    ) {}

    public function process(array $payload): PembayaranMahasiswa
    {
        // Bungkus dalam database transaction
        return DB::transaction(function () use ($payload) {

            // 1. Dapatkan Class Model (misal: App\Models\TagihanMahasiswa)
            $modelClass = Relation::getMorphedModel($payload['tagihan_type']);
            if (! $modelClass) {
                throw new Exception("Tipe tagihan tidak dikenali sistem: {$payload['tagihan_type']}");
            }

            // 2. PESSIMISTIC LOCKING: Kunci baris tagihan selama eksekusi
            // Ini mencegah bypass / double click pada milisecond yang sama
            $tagihan = $modelClass::lockForUpdate()->findOrFail($payload['tagihan_id']);

            // 3. SECURITY: Cegah paksaan upload jika sudah LUNAS
            if ($tagihan->status_bayar === 'LUNAS') {
                throw new Exception("Tagihan ini sudah berstatus LUNAS. Pembayaran tidak dapat diproses.");
            }

            // 4. SECURITY: Cegah spam upload jika ada status PENDING manual
            $adaPending = PembayaranMahasiswa::where('tagihan_id', $tagihan->id)
                ->where('tagihan_type', $payload['tagihan_type'])
                ->where('status_verifikasi_id', StatusVerifikasiPembayaran::PENDING)
                ->where('metode_pembayaran', MetodePembayaran::MANUAL)
                ->exists();

            if ($adaPending) {
                throw new Exception("Anda masih memiliki bukti transfer yang sedang diverifikasi.");
            }

            // 5. Susun DTO dan teruskan ke Global Intake Service
            $dto = PembayaranIntakeData::make(
                tagihanId: $payload['tagihan_id'],
                tagihanType: $payload['tagihan_type'],
                nominalBayar: $payload['nominal_bayar'],
                tanggalBayar: Carbon::parse($payload['tanggal_bayar']),
                metodePembayaran: MetodePembayaran::MANUAL,
                idempotencyKey: null,
                buktiBayarPath: $payload['bukti_bayar_path'] ?? null,

                // Tambahkan ini jika Anda sudah update properti DTO untuk menampung ID Bank Kampus
                bankKampusId: $payload['bank_tujuan_id'] ?? null,

                keteranganPengirim: $payload['catatan'] ?? 'Diunggah mandiri oleh Mahasiswa'
            );

            return $this->intakeService->catat($dto);
        });
    }
}
