<?php

declare(strict_types=1);

namespace App\Services\Keuangan;

use App\Enums\Keuangan\StatusAdjustment;
use App\Enums\Keuangan\TindakLanjutKelebihanBayar;
use App\Exceptions\Keuangan\AdjustmentException;
use App\Models\KeuanganAdjustment;
use App\Models\KeuanganGeneralLedger;
use App\Models\KeuanganSaldo;
use App\Models\KeuanganSaldoTransaction;
use App\Models\TagihanMahasiswa;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdjustmentPostingService
{
    /**
     * Mengeksekusi posting adjustment ke Tagihan dan General Ledger.
     * Menggunakan Pessimistic Locking untuk mencegah race condition.
     * * @throws AdjustmentException
     */
    public function posting(KeuanganAdjustment $adjustment, User $postedBy): void
    {
        // Pastikan status legal melalui State Machine
        app(AdjustmentStateMachine::class)->assertCanTransition($adjustment, StatusAdjustment::DIPOSTING, $postedBy);

        DB::transaction(function () use ($adjustment, $postedBy): void {
            // 1. Lock tagihan untuk update
            $tagihan = TagihanMahasiswa::where('id', $adjustment->tagihan_id)->lockForUpdate()->firstOrFail();

            // 2. Cegah Stale Data (Logical Race Condition)
            // Jika tagihan di-update (misal ada pembayaran masuk) setelah adjustment diajukan, tolak eksekusi.
            if ($adjustment->diajukan_at && $tagihan->updated_at > $adjustment->diajukan_at) {
                throw new AdjustmentException('Tagihan telah mengalami perubahan sejak adjustment ini diajukan. Harap tolak adjustment ini dan buat pengajuan ulang.');
            }

            // 3. Kalkulasi Tagihan Baru
            $oldTotalTagihan = (float) $tagihan->total_tagihan;
            $newTotalTagihan = $oldTotalTagihan + (float) $adjustment->nominal;

            if ($newTotalTagihan < 0) {
                throw new AdjustmentException('Nominal adjustment mengakibatkan total tagihan menjadi negatif. Operasi dibatalkan.');
            }

            $kelebihanBayar = 0.0;
            $totalBayar = (float) $tagihan->total_bayar;

            // 4. Deteksi Overpayment & Penyesuaian total_bayar
            if ($totalBayar > $newTotalTagihan) {
                $kelebihanBayar = $totalBayar - $newTotalTagihan;
                
                if ($adjustment->tindak_lanjut_kelebihan_bayar === TindakLanjutKelebihanBayar::TIDAK_ADA) {
                    throw new AdjustmentException('Adjustment ini menyebabkan kelebihan bayar, wajib memilih tindak lanjut (Saldo/Refund).');
                }

                // Turunkan total bayar agar sisa_tagihan tidak menjadi minus
                $tagihan->total_bayar = $newTotalTagihan;
                $tagihan->status_bayar = 'LUNAS';
                
                $this->prosesKelebihanBayar($tagihan->mahasiswa_id, $kelebihanBayar, $adjustment);
            } else {
                // Update status bayar untuk kasus normal / penambahan tagihan
                $tagihan->status_bayar = $this->kalkulasiStatusBayar($tagihan->total_bayar, $newTotalTagihan);
            }

            // 5. Update Tagihan Header
            $tagihan->total_tagihan = $newTotalTagihan;
            $tagihan->save();

            // 6. Catat ke General Ledger (Buku Besar)
            $saldoBerjalan = $this->hitungSaldoBerjalanTerakhir($tagihan->mahasiswa_id);
            $nominalAdj = (float) $adjustment->nominal;
            
            // Nominal positif = Debit (Penambahan piutang/tagihan institusi), Negatif = Kredit
            $debit = $nominalAdj > 0 ? $nominalAdj : 0;
            $kredit = $nominalAdj < 0 ? abs($nominalAdj) : 0;
            
            KeuanganGeneralLedger::create([
                'mahasiswa_id' => $tagihan->mahasiswa_id,
                'referensi_dokumen' => $adjustment->nomor_adjustment,
                'tipe_transaksi' => 'ADJUSTMENT',
                'debit' => $debit,
                'kredit' => $kredit,
                'saldo_berjalan' => $saldoBerjalan + $debit - $kredit,
                'keterangan' => 'Penyesuaian tagihan: ' . $adjustment->keterangan,
            ]);

            // 7. Finalisasi Status Adjustment
            $adjustment->update([
                'status' => StatusAdjustment::DIPOSTING,
                'diposting_at' => now(),
            ]);
        });
    }

    /**
     * Mengambil saldo berjalan terakhir dari Ledger Mahasiswa.
     */
    public function hitungSaldoBerjalanTerakhir(string $mahasiswaId): float
    {
        $lastLedger = KeuanganGeneralLedger::where('mahasiswa_id', $mahasiswaId)
            ->orderByDesc('created_at')
            ->orderByDesc('id') // Tie-breaker
            ->first();

        return $lastLedger ? (float) $lastLedger->saldo_berjalan : 0.0;
    }

    /**
     * Memproses uang kelebihan akibat pengurangan tagihan (beasiswa retroaktif).
     */
    private function prosesKelebihanBayar(string $mahasiswaId, float $kelebihanBayar, KeuanganAdjustment $adjustment): void
    {
        if ($adjustment->tindak_lanjut_kelebihan_bayar === TindakLanjutKelebihanBayar::SALDO_KREDIT) {
            // Lock deposit record
            $saldo = KeuanganSaldo::firstOrCreate(['mahasiswa_id' => $mahasiswaId]);
            // Lock row manual (karena eloquent model tidak di query via lockForUpdate di firstOrCreate)
            $saldo = KeuanganSaldo::where('id', $saldo->id)->lockForUpdate()->first();
            
            $saldo->increment('saldo', $kelebihanBayar);
            $saldo->update(['last_updated_at' => now()]);

            KeuanganSaldoTransaction::create([
                'saldo_id' => $saldo->id,
                'tipe' => 'IN',
                'nominal' => $kelebihanBayar,
                'referensi_id' => $adjustment->nomor_adjustment,
                'keterangan' => 'Kompensasi overpayment dari adjustment: ' . $adjustment->nomor_adjustment,
            ]);
        } elseif ($adjustment->tindak_lanjut_kelebihan_bayar === TindakLanjutKelebihanBayar::REFUND_TUNAI) {
            // Catat refund sebagai kewajiban transfer di ledger
            $saldoBerjalan = $this->hitungSaldoBerjalanTerakhir($mahasiswaId);
            
            KeuanganGeneralLedger::create([
                'mahasiswa_id' => $mahasiswaId,
                'referensi_dokumen' => $adjustment->nomor_adjustment,
                'tipe_transaksi' => 'REFUND',
                'debit' => 0,
                'kredit' => $kelebihanBayar,
                'saldo_berjalan' => $saldoBerjalan - $kelebihanBayar,
                'keterangan' => 'Pengembalian tunai/transfer akibat adjustment: ' . $adjustment->nomor_adjustment,
            ]);
        }
    }

    private function kalkulasiStatusBayar(float $totalBayar, float $totalTagihan): string
    {
        if ($totalBayar >= $totalTagihan && $totalTagihan > 0) return 'LUNAS';
        if ($totalBayar > 0 && $totalBayar < $totalTagihan) return 'CICIL';
        return 'BELUM';
    }
}