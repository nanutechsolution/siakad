<?php

declare(strict_types=1);

namespace App\Services\Keuangan;

use App\Enums\Keuangan\StatusVerifikasiKode;
use App\Models\PembayaranMahasiswa;
use App\Models\RefStatusVerifikasiPembayaran;
use App\Models\TagihanMahasiswa;
use App\Models\KeuanganGeneralLedger;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PembayaranVerificationService
{
    /**
     * Memproses verifikasi pembayaran mahasiswa.
     */
    public function verifikasi(PembayaranMahasiswa $pembayaran, StatusVerifikasiKode $statusKode, User $verifier, ?string $catatan = null): void
    {
        DB::transaction(function () use ($pembayaran, $statusKode, $verifier, $catatan) {
            // 1. Lock record pembayaran untuk mencegah double-process
            $pembayaran = PembayaranMahasiswa::where('id', $pembayaran->id)->lockForUpdate()->first();
            
            // 2. Resolve Status ID dari database
            $statusRecord = RefStatusVerifikasiPembayaran::where('kode', $statusKode->value)->firstOrFail();

            $pembayaran->update([
                'status_verifikasi_id' => $statusRecord->id,
                'verified_by' => $verifier->id,
                'verified_at' => now(),
                'catatan_verifikasi' => $catatan,
            ]);

            // 3. Jika Valid, Update Tagihan & Ledger
            if ($statusKode === StatusVerifikasiKode::VALID) {
                $this->prosesPenerimaanPembayaran($pembayaran);
            }
        });
    }

    private function prosesPenerimaanPembayaran(PembayaranMahasiswa $pembayaran): void
    {
        // Lock tagihan terkait
        $tagihan = TagihanMahasiswa::where('id', $pembayaran->tagihan_id)->lockForUpdate()->firstOrFail();

        $nominalBayar = (float) $pembayaran->nominal_bayar;

        // Update total bayar header
        $tagihan->total_bayar += $nominalBayar;
        
        // Update status bayar
        if ($tagihan->total_bayar >= (float) $tagihan->total_tagihan) {
            $tagihan->status_bayar = 'LUNAS';
        } else {
            $tagihan->status_bayar = 'CICIL';
        }
        $tagihan->save();

        // --- PERBAIKAN: DISTRIBUSI PEMBAYARAN KE KOMPONEN BIAYA ---
        // Ambil sisa uang yang akan didistribusikan ke rincian
        $sisaAlokasi = $nominalBayar;

        // Ambil rincian tagihan (detail) yang masih punya kekurangan bayar
        // Catatan: Jika Anda memiliki field 'urutan_prioritas' di tabel komponen_biaya, 
        // Anda bisa melakukan JOIN dan menambahkan ->orderBy('urutan_prioritas', 'asc')
        $tagihanDetails = DB::table('tagihan_mahasiswas_details')
            ->where('tagihan_id', $tagihan->id)
            ->whereRaw('nominal_terbayar < nominal_tagihan')
            ->orderBy('id', 'asc') // Default urutan by ID
            ->get();

        foreach ($tagihanDetails as $detail) {
            if ($sisaAlokasi <= 0) {
                break; // Jika uang pembayaran sudah habis dialokasikan, hentikan proses
            }

            $kekuranganKomponen = (float) $detail->nominal_tagihan - (float) $detail->nominal_terbayar;

            if ($kekuranganKomponen > 0) {
                // Tentukan berapa nominal yang akan dialokasikan ke komponen ini
                // (pilih yang paling kecil antara sisa uang bayar vs kekurangan komponen)
                $alokasi = min($sisaAlokasi, $kekuranganKomponen);

                // Update nominal_terbayar pada detail komponen ini
                DB::table('tagihan_mahasiswas_details')
                    ->where('id', $detail->id)
                    ->update([
                        'nominal_terbayar' => DB::raw("nominal_terbayar + {$alokasi}")
                    ]);

                // Kurangi sisa alokasi uang bayar
                $sisaAlokasi -= $alokasi;
            }
        }
        // ---------------------------------------------------------

        // Catat ke General Ledger
        $lastLedger = KeuanganGeneralLedger::where('mahasiswa_id', $tagihan->mahasiswa_id)
            ->orderByDesc('created_at')
            ->first();
            
        $saldoBerjalan = $lastLedger ? (float) $lastLedger->saldo_berjalan : 0.0;

        KeuanganGeneralLedger::create([
            'id' => Str::uuid()->toString(),
            'mahasiswa_id' => $tagihan->mahasiswa_id,
            'referensi_dokumen' => 'PAY-' . $pembayaran->id,
            'tipe_transaksi' => 'PEMBAYARAN',
            'debit' => 0.00,
            'kredit' => $nominalBayar,
            'saldo_berjalan' => $saldoBerjalan - $nominalBayar,
            'keterangan' => 'Pembayaran tagihan ' . $tagihan->kode_transaksi . ' via ' . $pembayaran->metode_pembayaran,
            'created_at' => now(),
        ]);
    }
}