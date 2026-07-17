<?php

namespace App\Listeners\Pembayaran;

use App\Events\PembayaranTerverifikasi;
use App\Mail\CicilanTerverifikasiMailable;
use App\Models\Mahasiswa;
use App\Services\Notifications\SmsService;
use App\Services\Pembayaran\PaymentPolicyChecker;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class KirimNotifikasiPembayaranListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly PaymentPolicyChecker $policyChecker,
        private readonly SmsService $smsService,
    ) {}

    public function handle(PembayaranTerverifikasi $event): void
    {
        $pembayaran = $event->pembayaran;

        // Load relasi yang dibutuhkan untuk notifikasi agar tidak N+1 Query di dalam Queue Worker
        $pembayaran->loadMissing(['tagihan.mahasiswa.person']);
        $tagihan = $pembayaran->tagihan;
        $mahasiswa = $tagihan?->mahasiswa;

        if (!$mahasiswa) return;

        $compliance = $this->policyChecker->cekKepatuhan($mahasiswa, $tagihan);

        // Jika masih berstatus PMB dan belum lulus syarat
        if (Str::startsWith((string) $mahasiswa->nim, 'PMB') && !$compliance['passed']) {
            $this->kirimNotifikasiCicilan($mahasiswa, $compliance['unmet']);
        }
        // Jika sudah lulus syarat dan NIM sudah berubah (NIM diganti oleh GenerateNimListener sebelumnya)
        elseif (!Str::startsWith((string) $mahasiswa->nim, 'PMB') && $compliance['passed']) {
            $this->kirimNotifikasiAktivasiNim($mahasiswa, $mahasiswa->nim);
        }
    }

    /**
     * Mengirim notifikasi saat pembayaran dicicil tapi belum memenuhi policy
     */
    private function kirimNotifikasiCicilan(Mahasiswa $mahasiswa, array $unmet): void
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
        })->implode("\n");

        $body = "Cicilan Anda telah diverifikasi, tetapi belum memenuhi syarat pembayaran untuk aktivasi NIM.\n\n" .
            "Rincian yang belum terpenuhi:\n" . $summary .
            "\n\nSilakan selesaikan cicilan sesuai instruksi pembayaran agar NIM dapat diaktifkan.";

        // 1. Notifikasi Database (Filament)
        Notification::make()
            ->title('Cicilan Diverifikasi — Lengkapi Pembayaran')
            ->body($body)
            ->icon('heroicon-o-bell')
            ->persistent()
            ->actions([
                Action::make('lihat_tagihan')
                    ->label('Lihat Tagihan')
                    ->color('primary')
                    ->url(url('/mahasiswa/tagihan-mahasiswas'), shouldOpenInNewTab: false),
            ])
            ->sendToDatabase($user);

        // 2. Kirim Email (Aman memanggil ->send() karena class ini sudah ShouldQueue)
        if (! empty($user->email)) {
            try {
                Mail::to($user->email)->send(new CicilanTerverifikasiMailable($mahasiswa, $unmet));
            } catch (\Throwable $e) {
                Log::error('Gagal kirim email cicilan terverifikasi', [
                    'error' => $e->getMessage(),
                    'mahasiswa_id' => $mahasiswa->id
                ]);
            }
        }

        // 3. Kirim SMS
        $phone = $mahasiswa->person?->no_hp ?? null;
        if ($phone) {
            try {
                $this->smsService->send($phone, $body);
            } catch (\Throwable $e) {
                Log::error('Gagal kirim SMS cicilan terverifikasi', [
                    'error' => $e->getMessage(),
                    'mahasiswa_id' => $mahasiswa->id
                ]);
            }
        }
    }

    /**
     * Mengirim notifikasi saat NIM mahasiswa berhasil diterbitkan
     */
    private function kirimNotifikasiAktivasiNim(Mahasiswa $mahasiswa, string $nimBaru): void
    {
        $user = $mahasiswa->akunUser();

        if (! $user) {
            Log::warning('Gagal kirim notifikasi NIM: mahasiswa tidak punya akun User', [
                'mahasiswa_id' => $mahasiswa->id,
            ]);
            return;
        }

        // Notifikasi Database (Filament)
        Notification::make()
            ->title('Selamat! NIM Anda Telah Terbit')
            ->body("Pembayaran telah diverifikasi. NIM baru Anda adalah: {$nimBaru}.")
            ->icon('heroicon-o-academic-cap')
            ->persistent()
            ->sendToDatabase($user);
    }
}
