<?php

declare(strict_types=1);

namespace App\Services\Pembayaran;

use App\Models\Mahasiswa;
use App\Models\PaymentPolicy;
use App\Models\TagihanMahasiswa;
use App\Models\TagihanMahasiswaDetail;
use Illuminate\Support\Facades\DB;

/**
 * Mengecek apakah pembayaran mahasiswa sudah memenuhi Payment Policy
 * untuk komponen biaya yang wajib (mis. SPP minimal 50%).
 *
 * Dipakai oleh:
 *  - PembayaranMahasiswaObserver (gate generate NIM)
 *  - KrsValidationService::checkKeuangan() (gate KRS)
 */
class PaymentPolicyChecker
{
    /**
     * Cek apakah tagihan semester ini sudah memenuhi policy.
     *
     * @return array{passed: bool, unmet: array<int, array{nama: string, target: float, terbayar: float}>}
     */
    public function cekKepatuhan(Mahasiswa $mahasiswa, TagihanMahasiswa $tagihan): array
    {
        // 1. Cari policy yang applicable
        $policy = $this->cariPolicyApplicable($mahasiswa, $tagihan);

        if (!$policy) {
            return ['passed' => false, 'unmet' => []];
        }

        // 2. Jika tagihan sudah LUNAS, otomatis pass
        if (strtoupper($tagihan->status_bayar) === 'LUNAS') {
            return ['passed' => true, 'unmet' => []];
        }

        // 3. Evaluasi per komponen wajib
        $unmet = [];
        $details = $policy->details()->where('wajib', true)->get();

        foreach ($details as $detail) {
            $realisasi = TagihanMahasiswaDetail::where('tagihan_id', $tagihan->id)
                ->where('komponen_biaya_id', $detail->komponen_biaya_id)
                ->first();

            if (!$realisasi) {
                $unmet[] = [
                    'nama' => $detail->komponenBiaya?->nama_komponen ?? 'Unknown',
                    'target' => 0.0,
                    'terbayar' => 0.0,
                ];
                continue;
            }

            $nominalTerbayar = (float) $realisasi->nominal_terbayar;
            $nominalTagihan = (float) $realisasi->nominal_tagihan;

            $minimumSesuaiPersen = $nominalTagihan * ((float) $detail->minimal_persen / 100);
            $minimumNominal = (float) $detail->minimal_nominal;

            $targetBayar = $minimumNominal > 0 ? $minimumNominal : $minimumSesuaiPersen;

            if ($nominalTerbayar < $targetBayar) {
                $unmet[] = [
                    'nama' => $detail->komponenBiaya?->nama_komponen ?? 'Unknown',
                    'target' => $targetBayar,
                    'terbayar' => $nominalTerbayar,
                ];
            }
        }

        return [
            'passed' => empty($unmet),
            'unmet' => $unmet,
        ];
    }

    private function cariPolicyApplicable(Mahasiswa $mahasiswa, TagihanMahasiswa $tagihan): ?PaymentPolicy
    {
        return PaymentPolicy::query()
            ->where('tahun_akademik_id', $tagihan->tahun_akademik_id)
            ->where('aktif', 1)
            ->where(function ($q) use ($mahasiswa) {
                $q->where('prodi_id', $mahasiswa->prodi_id)->orWhereNull('prodi_id');
            })
            ->where(function ($q) use ($mahasiswa) {
                $q->where('program_kelas_id', $mahasiswa->program_id)->orWhereNull('program_kelas_id');
            })
            ->orderByRaw('prodi_id IS NULL, program_kelas_id IS NULL')
            ->first();
    }
}
