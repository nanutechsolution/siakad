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
use App\Services\Keuangan\LedgerService;
use Illuminate\Support\Facades\DB;

class AdjustmentPostingService
{
    public function __construct(
        private readonly LedgerService $ledger,
    ) {}

    /**
     * Mengeksekusi posting adjustment ke Tagihan dan General Ledger.
     * Menggunakan Pessimistic Locking untuk mencegah race condition.
     * * @throws AdjustmentException
     */
    public function posting(KeuanganAdjustment $adjustment, User $postedBy): void
    {
        DB::transaction(function () use ($adjustment, $postedBy): void {
            // 0. Lock BARIS ADJUSTMENT-nya dan baca status terbaru.
            // Tanpa ini, dua request "Post" paralel keduanya lolos validasi
            // state machine (DISETUJUI) lalu sama-sama posting → tagihan
            // terpotong dua kali dan ledger dapat dua entri.
            $lockedAdjustment = KeuanganAdjustment::query()
                ->whereKey($adjustment->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            app(AdjustmentStateMachine::class)->assertCanTransition(
                $lockedAdjustment,
                StatusAdjustment::DIPOSTING,
                $postedBy
            );

            // Lanjutkan dengan instance terkunci supaya seluruh pembacaan
            // (nominal, nomor, tindak lanjut, status) dari baris terbaru.
            $adjustment = $lockedAdjustment;

            // 1. Lock tagihan untuk update
            $tagihan = TagihanMahasiswa::where('id', $lockedAdjustment->tagihan_id)->lockForUpdate()->firstOrFail();

            // 2. Cegah Stale Data (Logical Race Condition)
            // Jika tagihan di-update (misal ada pembayaran masuk) setelah adjustment diajukan, tolak eksekusi.
            if ($lockedAdjustment->diajukan_at && $tagihan->updated_at > $lockedAdjustment->diajukan_at) {
                throw new AdjustmentException('Tagihan telah mengalami perubahan sejak adjustment ini diajukan. Harap tolak adjustment ini dan buat pengajuan ulang.');
            }

            // =========================================================================
            // 3. UPDATE RINCIAN/DETAIL TAGIHAN BERDASARKAN KOMPONEN BIAYA
            // =========================================================================
            if ($lockedAdjustment->komponen_biaya_id) {
                $detailTagihan = DB::table('tagihan_mahasiswas_details')
                    ->where('tagihan_id', $tagihan->id)
                    ->where('komponen_biaya_id', $lockedAdjustment->komponen_biaya_id)
                    ->lockForUpdate()
                    ->first();

                if ($detailTagihan) {
                    // Tambahkan nilai nominal adjustment ke nominal dasar yang ada di detail
                    $nominalBaruDetail = bcadd((string) $detailTagihan->nominal_dasar, (string) $adjustment->nominal, 2);

                    if (bccomp($nominalBaruDetail, '0.00', 2) < 0) {
                        throw new AdjustmentException('Nominal komponen detail tagihan menjadi negatif setelah penyesuaian. Operasi dibatalkan.');
                    }

                    DB::table('tagihan_mahasiswas_details')
                        ->where('id', $detailTagihan->id)
                        ->update([
                            'nominal_dasar' => $nominalBaruDetail,
                            'updated_at' => now(),
                        ]);
                }
            }
            // =========================================================================

            // 4. Kalkulasi Tagihan Baru (Header)
            $oldTotalTagihan = (string) $tagihan->total_tagihan;
            $newTotalTagihan = bcadd($oldTotalTagihan, (string) $adjustment->nominal, 2);

            if (bccomp($newTotalTagihan, '0.00', 2) < 0) {
                throw new AdjustmentException('Nominal adjustment mengakibatkan total tagihan menjadi negatif. Operasi dibatalkan.');
            }

            $kelebihanBayar = '0.00';
            $totalBayar = (string) $tagihan->total_bayar;

            // 5. Deteksi Overpayment & Penyesuaian total_bayar
            if (bccomp($totalBayar, $newTotalTagihan, 2) === 1) {
                $kelebihanBayar = bcsub($totalBayar, $newTotalTagihan, 2);

                if ($adjustment->tindak_lanjut_kelebihan_bayar === TindakLanjutKelebihanBayar::TIDAK_ADA) {
                    throw new AdjustmentException('Adjustment ini menyebabkan kelebihan bayar, wajib memilih tindak lanjut (Saldo/Refund).');
                }

                // Turunkan total bayar agar sisa_tagihan tidak menjadi minus
                $tagihan->total_bayar = $newTotalTagihan;
                $tagihan->status_bayar = 'LUNAS';

                $this->prosesKelebihanBayar($tagihan->mahasiswa_id, $kelebihanBayar, $adjustment);
            } else {
                // Update status bayar untuk kasus normal / penambahan tagihan
                $tagihan->status_bayar = $this->kalkulasiStatusBayar(
                    $totalBayar,
                    $newTotalTagihan,
                );
            }

            // 6. Update Tagihan Header
            $tagihan->total_tagihan = $newTotalTagihan;
            $tagihan->save();

            // Adjustment nominal 0 tidak menggerakkan uang — jangan tulis baris
            // ledger, karena LedgerService melarang debit dan kredit sama-sama 0.
            if (bccomp((string) $adjustment->nominal, '0.00', 2) !== 0) {
                $this->ledger->recordKoreksi(
                    mahasiswaId: $tagihan->mahasiswa_id,
                    nominal: bccomp((string) $adjustment->nominal, '0.00', 2) > 0
                        ? (string) $adjustment->nominal
                        : bcmul((string) $adjustment->nominal, '-1', 2),
                    arah: bccomp((string) $adjustment->nominal, '0.00', 2) > 0 ? 'TAMBAH' : 'KURANG',
                    referensiDokumen: $adjustment->nomor_adjustment,
                    keterangan: 'Penyesuaian tagihan: ' . $adjustment->keterangan,
                );
            }

            // 8. Finalisasi Status Adjustment
            $adjustment->update([
                'status' => StatusAdjustment::DIPOSTING,
                'diposting_at' => now(),
            ]);
        });
    }

    /**
     * Memproses uang kelebihan akibat pengurangan tagihan (beasiswa retroaktif).
     */
    private function prosesKelebihanBayar(string $mahasiswaId, string $kelebihanBayar, KeuanganAdjustment $adjustment): void
    {
        if ($adjustment->tindak_lanjut_kelebihan_bayar === TindakLanjutKelebihanBayar::SALDO_KREDIT) {
            // Lock deposit record
            $saldo = KeuanganSaldo::firstOrCreate(['mahasiswa_id' => $mahasiswaId]);
            // Lock row manual (karena eloquent model tidak di query via lockForUpdate di firstOrCreate)
            $saldo = KeuanganSaldo::where('id', $saldo->id)->lockForUpdate()->first();

            $saldo->saldo = bcadd((string) $saldo->saldo, $kelebihanBayar, 2);
            $saldo->save();
            $saldo->update(['last_updated_at' => now()]);

            KeuanganSaldoTransaction::create([
                'saldo_id' => $saldo->id,
                'tipe' => 'IN',
                'nominal' => $kelebihanBayar,
                'referensi_id' => $adjustment->nomor_adjustment,
                'keterangan' => 'Kompensasi overpayment dari adjustment: ' . $adjustment->nomor_adjustment,
            ]);
        } elseif ($adjustment->tindak_lanjut_kelebihan_bayar === TindakLanjutKelebihanBayar::REFUND_TUNAI) {
            // Catat refund sebagai kewajiban transfer di ledger — lewat
            // LedgerService (satu-satunya pintu tulis) supaya urutan
            // saldo_berjalan dan idempotency konsisten dengan jalur lain.
            $this->ledger->recordRefund(
                mahasiswaId: $mahasiswaId,
                nominal: number_format($kelebihanBayar, 2, '.', ''),
                referensiDokumen: $adjustment->nomor_adjustment,
                keterangan: 'Pengembalian tunai/transfer akibat adjustment: ' . $adjustment->nomor_adjustment,
            );
        }
    }

    private function kalkulasiStatusBayar(string $totalBayar, string $totalTagihan): string
    {
        if (bccomp($totalBayar, $totalTagihan, 2) >= 0 && bccomp($totalTagihan, '0.00', 2) > 0) return 'LUNAS';
        if (bccomp($totalBayar, '0.00', 2) > 0 && bccomp($totalBayar, $totalTagihan, 2) < 0) return 'CICIL';
        return 'BELUM';
    }
}
