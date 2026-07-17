<?php

namespace App\Services\Keuangan;

use App\Enums\TipeTransaksiLedger;
use App\Models\KeuanganGeneralLedger;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Satu-satunya pintu penulisan ke keuangan_general_ledgers.
 *
 * Semua proses yang mempengaruhi piutang mahasiswa — generate tagihan,
 * verifikasi pembayaran, potongan, beasiswa, denda, refund, koreksi
 * transaksi — WAJIB lewat service ini, bukan insert langsung ke model
 * KeuanganGeneralLedger. Ini supaya saldo_berjalan selalu konsisten dan
 * urutannya benar meski ada beberapa proses berjalan bersamaan.
 *
 * DESAIN PENTING:
 *
 * 1. Locking per mahasiswa (Cache::lock, pakai tabel `cache_locks` yang
 *    sudah ada di schema Anda). saldo_berjalan dihitung dari baris
 *    terakhir milik mahasiswa yang sama — kalau dua proses menulis
 *    ledger mahasiswa yang sama secara bersamaan tanpa lock ini, salah
 *    satu bisa menghitung saldo_berjalan dari data basi (race
 *    condition), karena row-lock biasa (`lockForUpdate` pada baris
 *    terakhir) tidak menjamin urutan pada pola "baca-lalu-insert" pada
 *    tabel append-only seperti ini.
 *
 * 2. Idempotency lewat kombinasi (referensi_dokumen, tipe_transaksi).
 *    Kalau proses yang sama dipanggil ulang (retry job, double submit,
 *    webhook direplay), tidak akan dobel catat — dikembalikan entri
 *    yang sudah ada.
 *
 * 3. Setiap entri wajib mengisi TEPAT SATU dari debit/kredit. Ini
 *    memaksa pemanggil eksplisit soal arah transaksi, bukan menebak.
 */
class LedgerService
{
    /**
     * Primitif paling dasar. Method recordXxx() di bawah ini semuanya
     * memanggil ini — pakai langsung hanya kalau tidak ada helper yang
     * cocok.
     */
    public function record(
        string $mahasiswaId,
        TipeTransaksiLedger $tipeTransaksi,
        string $debit,
        string $kredit,
        string $referensiDokumen,
        string $keterangan,
    ): KeuanganGeneralLedger {
        $this->validasiArahTransaksi($debit, $kredit);

        return Cache::lock("ledger-mahasiswa:{$mahasiswaId}", 10)
            ->block(5, fn() => DB::transaction(function () use (
                $mahasiswaId,
                $tipeTransaksi,
                $debit,
                $kredit,
                $referensiDokumen,
                $keterangan
            ) {
                $existing = $this->cariEntriIdempotent($referensiDokumen, $tipeTransaksi);
                if ($existing) {
                    return $existing;
                }

                $saldoSebelumnya = KeuanganGeneralLedger::query()
                    ->where('mahasiswa_id', $mahasiswaId)
                    ->orderByDesc('created_at')
                    ->orderByDesc('id')
                    ->value('saldo_berjalan') ?? '0.00';

                $saldoBerjalan = bcadd(bcsub((string) $saldoSebelumnya, $kredit, 2), $debit, 2);

                return KeuanganGeneralLedger::create([
                    'mahasiswa_id' => $mahasiswaId,
                    'referensi_dokumen' => $referensiDokumen,
                    'tipe_transaksi' => $tipeTransaksi,
                    'debit' => $debit,
                    'kredit' => $kredit,
                    'saldo_berjalan' => $saldoBerjalan,
                    'keterangan' => $keterangan,
                ]);
            }));
    }

    /**
     * Tagihan baru terbit (semester ATAU non reguler) → menambah piutang.
     */
    public function recordTagihan(string $mahasiswaId, string $nominal, string $referensiDokumen, string $keterangan): KeuanganGeneralLedger
    {
        return $this->record($mahasiswaId, TipeTransaksiLedger::TAGIHAN, debit: $nominal, kredit: '0.00', referensiDokumen: $referensiDokumen, keterangan: $keterangan);
    }

    /**
     * Pembayaran mahasiswa TERVERIFIKASI → mengurangi piutang.
     * Panggil ini di titik VERIFIKASI, bukan di intake — pembayaran yang
     * masih PENDING belum boleh mempengaruhi saldo piutang.
     */
    public function recordPembayaran(string $mahasiswaId, string $nominal, string $referensiDokumen, string $keterangan): KeuanganGeneralLedger
    {
        return $this->record($mahasiswaId, TipeTransaksiLedger::PEMBAYARAN, debit: '0.00', kredit: $nominal, referensiDokumen: $referensiDokumen, keterangan: $keterangan);
    }

    /**
     * Potongan administratif di luar diskon komponen (mis. potongan
     * kebijakan khusus yang diputuskan lewat keuangan_adjustments) →
     * mengurangi piutang.
     */
    public function recordPotongan(string $mahasiswaId, string $nominal, string $referensiDokumen, string $keterangan): KeuanganGeneralLedger
    {
        return $this->record($mahasiswaId, TipeTransaksiLedger::ADJUSTMENT, debit: '0.00', kredit: $nominal, referensiDokumen: $referensiDokumen, keterangan: $keterangan);
    }

    /**
     * Beasiswa yang diterapkan RETROAKTIF ke tagihan yang sudah terbit
     * (bukan yang sudah baked-in sebagai nominal_diskon saat generate)
     * → mengurangi piutang.
     */
    public function recordBeasiswa(string $mahasiswaId, string $nominal, string $referensiDokumen, string $keterangan): KeuanganGeneralLedger
    {
        return $this->record($mahasiswaId, TipeTransaksiLedger::ADJUSTMENT, debit: '0.00', kredit: $nominal, referensiDokumen: $referensiDokumen, keterangan: $keterangan);
    }

    /**
     * Denda/keterlambatan → menambah piutang.
     */
    public function recordDenda(string $mahasiswaId, string $nominal, string $referensiDokumen, string $keterangan): KeuanganGeneralLedger
    {
        return $this->record($mahasiswaId, TipeTransaksiLedger::ADJUSTMENT, debit: $nominal, kredit: '0.00', referensiDokumen: $referensiDokumen, keterangan: $keterangan);
    }

    /**
     * Refund kelebihan bayar ke mahasiswa → uang dikembalikan, jadi
     * "kredit" yang tadinya tercatat dari pembayaran dibatalkan
     * sebagian/seluruhnya → didebit lagi di ledger.
     */
    public function recordRefund(string $mahasiswaId, string $nominal, string $referensiDokumen, string $keterangan): KeuanganGeneralLedger
    {
        return $this->record($mahasiswaId, TipeTransaksiLedger::REFUND, debit: $nominal, kredit: '0.00', referensiDokumen: $referensiDokumen, keterangan: $keterangan);
    }

    /**
     * Koreksi transaksi manual oleh admin keuangan. $arah wajib 'TAMBAH'
     * (piutang bertambah) atau 'KURANG' (piutang berkurang) — dibuat
     * eksplisit di pemanggilan supaya tidak ada tebak-tebakan soal arah
     * saat audit.
     */
    public function recordKoreksi(string $mahasiswaId, string $nominal, string $arah, string $referensiDokumen, string $keterangan): KeuanganGeneralLedger
    {
        return match ($arah) {
            'TAMBAH' => $this->record($mahasiswaId, TipeTransaksiLedger::ADJUSTMENT, debit: $nominal, kredit: '0.00', referensiDokumen: $referensiDokumen, keterangan: $keterangan),
            'KURANG' => $this->record($mahasiswaId, TipeTransaksiLedger::ADJUSTMENT, debit: '0.00', kredit: $nominal, referensiDokumen: $referensiDokumen, keterangan: $keterangan),
            default => throw new RuntimeException("Arah koreksi tidak valid: {$arah}. Harus 'TAMBAH' atau 'KURANG'."),
        };
    }

    private function validasiArahTransaksi(string $debit, string $kredit): void
    {
        $debitPositif = bccomp($debit, '0.00', 2) > 0;
        $kreditPositif = bccomp($kredit, '0.00', 2) > 0;

        if ($debitPositif === $kreditPositif) {
            throw new RuntimeException('Setiap entri ledger wajib mengisi TEPAT SATU dari debit atau kredit dengan nilai > 0 (tidak boleh keduanya nol, tidak boleh keduanya terisi).');
        }
    }

    private function cariEntriIdempotent(string $referensiDokumen, TipeTransaksiLedger $tipeTransaksi): ?KeuanganGeneralLedger
    {
        return KeuanganGeneralLedger::query()
            ->where('referensi_dokumen', $referensiDokumen)
            ->where('tipe_transaksi', $tipeTransaksi)
            ->first();
    }
}
