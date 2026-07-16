<?php

namespace App\Observers;

use App\Enums\StatusVerifikasiPembayaran;
use App\Models\Mahasiswa;
use App\Models\PembayaranMahasiswa;
use App\Models\RefProdi;
use App\Services\Pembayaran\PaymentPolicyChecker;
use App\Services\Pembayaran\PembayaranAllocationService;
use App\Mail\CicilanTerverifikasiMailable;
use App\Services\Notifications\SmsService;
use Illuminate\Support\Facades\Mail;
use App\Settings\KampusSettings;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PembayaranMahasiswaObserver
{
    public function __construct(
        private readonly PembayaranAllocationService $allocationService,
        private readonly PaymentPolicyChecker $policyChecker,
    ) {}

    public function updated(PembayaranMahasiswa $pembayaran): void
    {
        // Hanya jalan jika status_verifikasi_id benar-benar berubah
        if (! $pembayaran->wasChanged('status_verifikasi_id')) {
            return;
        }

        // Hanya proses saat status menjadi VERIFIED
        if ($pembayaran->status_verifikasi_id !== StatusVerifikasiPembayaran::VERIFIED) {
            return;
        }

        Log::info('Observer PembayaranMahasiswa: status berubah ke VERIFIED', [
            'pembayaran_id' => $pembayaran->id,
        ]);

        // 1. Jalankan alokasi pembayaran (FIFO ke komponen, update status_bayar)
        //    PembayaranVerificationService sudah memanggil ini di transaksinya sendiri,
        //    tapi kita panggil lagi di sini agar observer berdiri sendiri & idempotent.
        try {
            DB::transaction(function () use ($pembayaran) {
                $this->allocationService->alokasikan($pembayaran);
            });
        } catch (\Throwable $e) {
            Log::error('Observer: gagal alokasi pembayaran', [
                'pembayaran_id' => $pembayaran->id,
                'error' => $e->getMessage(),
            ]);
            // Jangan hentikan proses generate NIM hanya karena alokasi gagal
        }

        // 2. Generate NIM resmi jika mahasiswa masih berstatus Camaba (NIM berawalan PMB)
        $pembayaran->loadMissing(['tagihan.mahasiswa.prodi']);

        $tagihan = $pembayaran->tagihan;
        $mahasiswa = $tagihan?->mahasiswa;

        if (! $mahasiswa) {
            return;
        }

        if (! Str::startsWith((string) $mahasiswa->nim, 'PMB')) {
            // Bukan Camaba, observer tidak relevan
            return;
        }
        $compliance = $this->policyChecker->cekKepatuhan($mahasiswa, $tagihan);

        if (!$compliance['passed']) {
            Log::info('Observer: NIM belum di-generate, policy belum terpenuhi', [
                'mahasiswa_id' => $mahasiswa->id,
                'unmet' => $compliance['unmet'],
            ]);
            // Kirim notifikasi ke akun mahasiswa supaya tahu cicilan sudah diverifikasi
            $this->kirimNotifikasiCicilanTerverifikasi($mahasiswa, $compliance['unmet']);
            return;
        }
        try {
            $nimBaru = DB::transaction(function () use ($mahasiswa) {
                return $this->generateNimUntukCamaba($mahasiswa);
            });

            // 3. Kirim notifikasi aktivasi NIM
            $this->kirimNotifikasiAktivasi($mahasiswa, $nimBaru);

            Log::info('NIM Generated', [
                'mahasiswa_id' => $mahasiswa->id,
                'nim' => $nimBaru,
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal Auto-Generate NIM Camaba', [
                'mahasiswa_id' => $mahasiswa->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate NIM resmi untuk Camaba dengan strategi:
     *  - Skenario A (reset per tahun): ambil NIM terakhir di (prodi, angkatan) yg bukan PMB
     *  - Skenario B (global counter): pakai prodi.last_nim_seq + 1
     *
     * Format NIM dibaca dari ref_prodi.format_nim, mendukung placeholder:
     *  - {TAHUN}  -> tahun 4 digit (2026)
     *  - {THN}    -> 2 digit terakhir tahun (26)
     *  - {KODE}   -> kode_prodi_internal
     *  - {NO:X}   -> nomor urut di-pad X digit (mis. {NO:3} -> 001)
     *  - {NO}     -> fallback 3 digit
     */
    private function generateNimUntukCamaba(Mahasiswa $mahasiswa): string
    {
        $prodi = $mahasiswa->prodi;
        $angkatanTahun = (int) $mahasiswa->angkatan_id;

        if (! $prodi instanceof RefProdi) {
            throw new \RuntimeException("Mahasiswa ID {$mahasiswa->id} tidak memiliki prodi yang valid.");
        }

        $kampusSettings = app(KampusSettings::class);
        $isResetPerTahun = (bool) $kampusSettings->reset_nim_tahunan;

        // Tentukan nomor urut berikutnya dengan lock agar aman dari race condition
        $nextSeq = $this->tentukanNomorUrut($prodi, $angkatanTahun, $isResetPerTahun);

        // Susun NIM dari format
        $nim = $this->renderFormatNim(
            $prodi->format_nim ?? '{THN}{KODE}{NO:3}',
            $angkatanTahun,
            $prodi->kode_prodi_internal,
            $nextSeq
        );

        // Update mahasiswa + counter prodi
        $mahasiswa->update(['nim' => $nim]);
        $prodi->update(['last_nim_seq' => $nextSeq]);

        return $nim;
    }

    /**
     * Tentukan nomor urut berikutnya dengan SELECT ... FOR UPDATE
     * agar concurrent verification tidak menghasilkan NIM kembar.
     */
    private function tentukanNomorUrut(RefProdi $prodi, int $angkatanTahun, bool $isResetPerTahun): int
    {
        if ($isResetPerTahun) {
            // Lock baris prodi agar counter aman
            $prodiLocked = RefProdi::whereKey($prodi->id)->lockForUpdate()->first();

            $lastMahasiswa = Mahasiswa::where('prodi_id', $prodiLocked->id)
                ->where('angkatan_id', $angkatanTahun)
                ->where('nim', 'NOT LIKE', 'PMB%')
                ->orderBy('nim', 'desc')
                ->lockForUpdate()
                ->first();

            $lastSeq = $lastMahasiswa ? (int) substr($lastMahasiswa->nim, -3) : 0;

            return $lastSeq + 1;
        }

        // Skenario global: cukup lock prodi lalu increment
        $prodiLocked = RefProdi::whereKey($prodi->id)->lockForUpdate()->first();

        return ((int) $prodiLocked->last_nim_seq) + 1;
    }

    /**
     * Render placeholder format_nim menjadi string NIM akhir.
     */
    private function renderFormatNim(string $format, int $tahun, string $kodeProdi, int $nomorUrut): string
    {
        $nim = $format;
        $nim = str_replace('{TAHUN}', (string) $tahun, $nim);
        $nim = str_replace('{THN}', substr((string) $tahun, -2), $nim);
        $nim = str_replace('{KODE}', $kodeProdi, $nim);

        if (preg_match('/\{NO:(\d+)\}/', $nim, $matches)) {
            $digitCount = max(1, (int) $matches[1]);
            $padded = str_pad((string) $nomorUrut, $digitCount, '0', STR_PAD_LEFT);
            $nim = str_replace($matches[0], $padded, $nim);
        } else {
            $nim = str_replace('{NO}', str_pad((string) $nomorUrut, 3, '0', STR_PAD_LEFT), $nim);
        }

        return $nim;
    }

    /**
     * Kirim notifikasi Filament ke user mahasiswa dengan tombol aktivasi NIM.
     */
    private function kirimNotifikasiAktivasi(Mahasiswa $mahasiswa, string $nimBaru): void
    {
        $user = $mahasiswa->akunUser();

        if (! $user) {
            Log::warning('Gagal kirim notifikasi NIM: mahasiswa tidak punya akun User', [
                'mahasiswa_id' => $mahasiswa->id,
            ]);

            return;
        }

        Notification::make()
            ->title('Selamat! NIM Anda Telah Terbit')
            ->body("Pembayaran telah diverifikasi. NIM baru Anda adalah: {$nimBaru}.")
            ->icon('heroicon-o-academic-cap')
            ->persistent()
            ->actions([
                Action::make('aktifkan_nim')
                    ->label('Aktifkan NIM Baru')
                    ->color('success')
                    ->button()
                    ->url(url('/mahasiswa/reauth?nim=' . $nimBaru), shouldOpenInNewTab: false),
            ])
            ->sendToDatabase($user);
    }

    /**
     * Kirim notifikasi ketika cicilan terverifikasi tetapi belum memenuhi payment policy.
     * Menyertakan ringkasan komponen yang belum terpenuhi agar mahasiswa tahu berapa yang perlu dilunasi.
     */
    private function kirimNotifikasiCicilanTerverifikasi(Mahasiswa $mahasiswa, array $unmet): void
    {
        $user = $mahasiswa->akunUser();

        if (! $user) {
            Log::warning('Gagal kirim notifikasi cicilan: mahasiswa tidak punya akun User', [
                'mahasiswa_id' => $mahasiswa->id,
            ]);
            return;
        }

        $summary = collect($unmet)->map(function ($u) {
            $nama = $u['nama'] ?? 'Komponen biaya';
            $terbayar = isset($u['terbayar']) ? number_format((float)$u['terbayar'], 0, ',', '.') : '0';
            $target = isset($u['target']) ? number_format((float)$u['target'], 0, ',', '.') : '0';
            return "{$nama}: Rp {$terbayar} / Rp {$target}";
        })->implode('\n');

        $body = "Cicilan Anda telah diverifikasi, tetapi belum memenuhi syarat pembayaran untuk aktivasi NIM.\n\n" .
            "Rincian yang belum terpenuhi:\n" . $summary .
            "\n\nSilakan selesaikan cicilan sesuai instruksi pembayaran agar NIM dapat diaktifkan.";

        // 1) Send database notification (Filament)
        Notification::make()
            ->title('Cicilan Diverifikasi — Lengkapi Pembayaran untuk Aktivasi NIM')
            ->body($body)
            ->icon('heroicon-o-bell')
            ->persistent()
            ->actions([
                Action::make('lihat_tagihan')
                    ->label('Lihat Tagihan')
                    ->color('primary')
                    ->url(url('/mahasiswa/tagihan'), shouldOpenInNewTab: false),
            ])
            ->sendToDatabase($user);

        // 2) Send Email (Mailable)
        if (! empty($user->email)) {
            try {
                Mail::to($user->email)
                    ->send(new CicilanTerverifikasiMailable($mahasiswa, $unmet));
            } catch (\Throwable $e) {
                Log::error('Gagal kirim email cicilan terverifikasi', ['error' => $e->getMessage(), 'mahasiswa_id' => $mahasiswa->id]);
            }
        }

        // 3) Send SMS (adapter)
        $phone = $mahasiswa->person?->no_hp ?? null;
        if ($phone) {
            try {
                app(SmsService::class)->send($phone, $body);
            } catch (\Throwable $e) {
                Log::error('Gagal kirim SMS cicilan terverifikasi', ['error' => $e->getMessage(), 'mahasiswa_id' => $mahasiswa->id]);
            }
        }
    }
}
