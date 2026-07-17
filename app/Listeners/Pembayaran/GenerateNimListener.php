<?php

namespace App\Listeners\Pembayaran;

use App\Events\PembayaranTerverifikasi;
use App\Models\Mahasiswa;
use App\Models\RefProdi;
use App\Services\Pembayaran\PaymentPolicyChecker;
use App\Settings\KampusSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GenerateNimListener
{
    public function __construct(
        private readonly PaymentPolicyChecker $policyChecker,
        private readonly KampusSettings $kampusSettings
    ) {}

    public function handle(PembayaranTerverifikasi $event): void
    {
        $pembayaran = $event->pembayaran;

        // Load relasi prodi agar tidak N+1 query
        $pembayaran->loadMissing(['tagihan.mahasiswa.prodi']);

        $tagihan = $pembayaran->tagihan;
        $mahasiswa = $tagihan?->mahasiswa;

        // Jika bukan Camaba (tidak berawalan PMB), abaikan
        if (!$mahasiswa || !Str::startsWith((string) $mahasiswa->nim, 'PMB')) {
            return;
        }

        // Nominal terbayar sekarang sudah akurat karena dibaca setelah AllocationService
        // $compliance = $this->policyChecker->cekKepatuhan($mahasiswa, $tagihan);
        if ($tagihan instanceof \App\Models\TagihanMahasiswa) {

            $compliance = $this->policyChecker
                ->cekKepatuhan($mahasiswa, $tagihan);
        } else {

            // Tagihan non reguler tidak memakai payment policy
            $compliance = [
                'passed' => true,
                'unmet' => [],
            ];
        }
        if ($compliance['passed']) {
            try {
                $nimBaru = DB::transaction(function () use ($mahasiswa) {
                    return $this->generateNim($mahasiswa);
                });

                Log::info('NIM Generated via Listener', [
                    'pembayaran_id' => $pembayaran->id,
                    'mahasiswa_id' => $mahasiswa->id,
                    'nim' => $nimBaru,
                ]);
            } catch (\Throwable $e) {
                Log::error('Gagal Auto-Generate NIM Camaba di Listener', [
                    'mahasiswa_id' => $mahasiswa->id,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        }
    }

    /**
     * Logika Generate NIM yang dipindahkan dari Observer lama
     */
    private function generateNim(Mahasiswa $mahasiswa): string
    {
        $prodi = $mahasiswa->prodi;
        $angkatanTahun = (int) $mahasiswa->angkatan_id;

        if (! $prodi instanceof RefProdi) {
            throw new \RuntimeException("Mahasiswa ID {$mahasiswa->id} tidak memiliki prodi yang valid.");
        }

        $isResetPerTahun = (bool) $this->kampusSettings->reset_nim_tahunan;

        // Tentukan nomor urut berikutnya
        $nextSeq = $this->tentukanNomorUrut($prodi, $angkatanTahun, $isResetPerTahun);

        // Susun NIM dari format
        $nim = $this->renderFormatNim(
            $prodi->format_nim ?? '{THN}{KODE}{NO:3}',
            $angkatanTahun,
            $prodi->kode_prodi_internal,
            $nextSeq
        );

        // Update mahasiswa & counter prodi
        $mahasiswa->update(['nim' => $nim]);
        $prodi->update(['last_nim_seq' => $nextSeq]);

        return $nim;
    }

    private function tentukanNomorUrut(RefProdi $prodi, int $angkatanTahun, bool $isResetPerTahun): int
    {
        // Lock baris prodi agar counter aman dari race condition
        $prodiLocked = RefProdi::whereKey($prodi->id)->lockForUpdate()->first();

        if ($isResetPerTahun) {
            $lastMahasiswa = Mahasiswa::where('prodi_id', $prodiLocked->id)
                ->where('angkatan_id', $angkatanTahun)
                ->where('nim', 'NOT LIKE', 'PMB%')
                ->orderBy('nim', 'desc')
                ->lockForUpdate()
                ->first();

            $lastSeq = $lastMahasiswa ? (int) substr($lastMahasiswa->nim, -3) : 0;

            return $lastSeq + 1;
        }

        // Skenario global: cukup pakai counter dari prodi lalu increment
        return ((int) $prodiLocked->last_nim_seq) + 1;
    }

    private function renderFormatNim(string $format, int $tahun, string $kodeProdi, int $nomorUrut): string
    {
        $nim = str_replace(
            ['{TAHUN}', '{THN}', '{KODE}'],
            [(string) $tahun, substr((string) $tahun, -2), $kodeProdi],
            $format
        );

        if (preg_match('/\{NO:(\d+)\}/', $nim, $matches)) {
            $digitCount = max(1, (int) $matches[1]);
            $padded = str_pad((string) $nomorUrut, $digitCount, '0', STR_PAD_LEFT);
            $nim = str_replace($matches[0], $padded, $nim);
        } else {
            $nim = str_replace('{NO}', str_pad((string) $nomorUrut, 3, '0', STR_PAD_LEFT), $nim);
        }

        return $nim;
    }
}
