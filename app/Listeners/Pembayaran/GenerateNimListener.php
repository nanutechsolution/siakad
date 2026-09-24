<?php

namespace App\Listeners\Pembayaran;

use App\Events\PembayaranTerverifikasi;
use App\Models\Mahasiswa;
use App\Models\RefProdi;
use App\Services\Akademik\NimService;
use App\Services\Pembayaran\PaymentPolicyChecker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GenerateNimListener
{
    public function __construct(
        private readonly PaymentPolicyChecker $policyChecker,
        private readonly NimService $nimService
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
     * Logika Generate NIM yang dipindahkan dari Observer lama.
     *
     * Render/format NIM ada di NimService agar mutasi prodi dan listener ini
     * tidak memiliki dua implementasi pola NIM yang bisa berbeda.
     */
    private function generateNim(Mahasiswa $mahasiswa): string
    {
        $prodi = $mahasiswa->prodi;

        if (! $prodi instanceof RefProdi) {
            throw new \RuntimeException("Mahasiswa ID {$mahasiswa->id} tidak memiliki prodi yang valid.");
        }

        $nim = $this->nimService->generate($mahasiswa, $prodi);

        $tahunAkademikMulai = \App\Models\RefTahunAkademik::query()
            ->where('kode_tahun', $mahasiswa->angkatan_id . '1')
            ->first();

        if (! $tahunAkademikMulai) {
            throw new \RuntimeException(
                "Tahun Akademik {$mahasiswa->angkatan_id}1 tidak ditemukan."
            );
        }

        $mahasiswa->update([
            'nim' => $nim,
            'mulai_studi_tahun_akademik_id' => $tahunAkademikMulai->id,
        ]);

        return $nim;
    }
}
