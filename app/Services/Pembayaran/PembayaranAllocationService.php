<?php

namespace App\Services\Pembayaran;

use App\Models\KeuanganSaldo;
use App\Models\KeuanganSaldoTransaction;
use App\Models\PembayaranMahasiswa;
use App\Models\TagihanMahasiswa;
use App\Models\TagihanMahasiswaDetail;
use App\Models\TagihanNonReguler;
use App\Models\TagihanNonRegulerDetail;

class PembayaranAllocationService
{
    /**
     * Satu-satunya method di seluruh aplikasi yang boleh mengubah
     * tagihan_mahasiswas.total_bayar / status_bayar dan keuangan_saldos.saldo.
     *
     * WAJIB dipanggil di dalam transaksi DB yang sama dengan
     * PembayaranVerificationService::verifikasi() — tidak untuk dipanggil
     * langsung dari Filament Action atau controller channel manapun.
     */

    public function alokasikan(PembayaranMahasiswa $pembayaran): void
    {
        $pembayaran->load('tagihan');

        $tagihan = $pembayaran->tagihan;

        if ($tagihan instanceof TagihanMahasiswa) {
            $this->alokasikanTagihanMahasiswa($pembayaran, $tagihan);
            return;
        }

        if ($tagihan instanceof TagihanNonReguler) {
            $this->alokasikanTagihanNonReguler($pembayaran, $tagihan);
            return;
        }

        throw new \RuntimeException('Jenis tagihan tidak dikenali.');
    }

    private function alokasikanTagihanMahasiswa(
        PembayaranMahasiswa $pembayaran,
        TagihanMahasiswa $tagihan
    ): void {
        $sisaSebelumAlokasi = bcsub(
            (string) $tagihan->total_tagihan,
            (string) $tagihan->total_bayar,
            2
        );

        $nominalBayar = (string) $pembayaran->nominal_bayar;

        $lebihDariSisa = bccomp($nominalBayar, $sisaSebelumAlokasi, 2) === 1;

        $dialokasikanKeTagihan = $lebihDariSisa
            ? $sisaSebelumAlokasi
            : $nominalBayar;

        $kelebihan = $lebihDariSisa
            ? bcsub($nominalBayar, $sisaSebelumAlokasi, 2)
            : '0.00';

        $totalBayarBaru = bcadd(
            (string) $tagihan->total_bayar,
            $dialokasikanKeTagihan,
            2
        );

        $tagihan->total_bayar = $totalBayarBaru;

        $tagihan->status_bayar = $this->tentukanStatusBayar(
            (string) $tagihan->total_tagihan,
            $totalBayarBaru
        );

        $tagihan->save();

        if (bccomp($dialokasikanKeTagihan, '0.00', 2) === 1) {
            $this->alokasikanKeDetailKomponen(
                $tagihan->id,
                $dialokasikanKeTagihan
            );
        }

        if (bccomp($kelebihan, '0.00', 2) === 1) {
            $this->catatKelebihanKeSaldo(
                $pembayaran,
                $tagihan,
                $kelebihan
            );
        }
    }
    /**
     * Membagi dana alokasi tagihan ke komponen biaya detail berdasarkan urutan prioritas (FIFO).
     */
    private function alokasikanKeDetailKomponen(string $tagihanId, string $danaAlokasi): void
    {
        // Ambil item detail, join ke keuangan_komponen_biaya untuk mengambil 'urutan_prioritas'
        $details = TagihanMahasiswaDetail::where('tagihan_id', $tagihanId)
            ->join('keuangan_komponen_biaya', 'tagihan_mahasiswas_details.komponen_biaya_id', '=', 'keuangan_komponen_biaya.id')
            ->select('tagihan_mahasiswas_details.*')
            ->orderBy('keuangan_komponen_biaya.urutan_prioritas', 'asc')
            ->lockForUpdate() // Kunci baris detail untuk konsistensi konkurensi data
            ->get();

        $sisaDana = $danaAlokasi;

        foreach ($details as $detail) {
            // Jika dana yang dialokasikan sudah habis dibagi, keluar dari loop
            if (bccomp($sisaDana, '0.00', 2) <= 0) {
                break;
            }

            // Hitung sisa tunggakan per komponen: (nominal_tagihan - nominal_terbayar)
            // Catatan: nominal_tagihan adalah Generated Column di MySQL (nominal_dasar - nominal_diskon)
            $sisaTunggakanKomponen = bcsub((string) $detail->nominal_tagihan, (string) $detail->nominal_terbayar, 2);

            // Jika komponen ini sudah lunas dari pembayaran sebelumnya, lewati
            if (bccomp($sisaTunggakanKomponen, '0.00', 2) <= 0) {
                continue;
            }

            // Tentukan nominal alokasi untuk komponen ini (pilih yang terkecil antara sisaDana atau sisaTunggakan)
            $alokasiKomponen = bccomp($sisaDana, $sisaTunggakanKomponen, 2) === 1 ? $sisaTunggakanKomponen : $sisaDana;

            // Update nominal_terbayar pada detail komponen
            $detail->nominal_terbayar = bcadd((string) $detail->nominal_terbayar, $alokasiKomponen, 2);
            $detail->save();

            // Kurangi sisa dana yang tersedia untuk komponen berikutnya
            $sisaDana = bcsub($sisaDana, $alokasiKomponen, 2);
        }
    }

    /**
     * Sama persis logikanya dengan alokasikanKeDetailKomponen() di atas,
     * hanya beda tabel target: tagihan_non_reguler_details, dan
     * nominal_tagihan di sini BUKAN generated column (lihat
     * TagihanNonRegulerService) — tapi nilainya sudah selalu diisi
     * eksplisit (nominal_dasar - nominal_diskon) saat tagihan dibuat,
     * jadi bisa dibaca langsung sama seperti kolom generated.
     */
    private function alokasikanKeDetailKomponenNonReguler(string $tagihanId, string $danaAlokasi): void
    {
        $details = TagihanNonRegulerDetail::where('tagihan_id', $tagihanId)
            ->join('keuangan_komponen_biaya', 'tagihan_non_reguler_details.komponen_biaya_id', '=', 'keuangan_komponen_biaya.id')
            ->select('tagihan_non_reguler_details.*')
            ->orderBy('keuangan_komponen_biaya.urutan_prioritas', 'asc')
            ->lockForUpdate()
            ->get();

        $sisaDana = $danaAlokasi;

        foreach ($details as $detail) {
            if (bccomp($sisaDana, '0.00', 2) <= 0) {
                break;
            }

            $sisaTunggakanKomponen = bcsub((string) $detail->nominal_tagihan, (string) $detail->nominal_terbayar, 2);

            if (bccomp($sisaTunggakanKomponen, '0.00', 2) <= 0) {
                continue;
            }

            $alokasiKomponen = bccomp($sisaDana, $sisaTunggakanKomponen, 2) === 1 ? $sisaTunggakanKomponen : $sisaDana;

            $detail->nominal_terbayar = bcadd((string) $detail->nominal_terbayar, $alokasiKomponen, 2);
            $detail->save();

            $sisaDana = bcsub($sisaDana, $alokasiKomponen, 2);
        }
    }

    private function tentukanStatusBayar(string $totalTagihan, string $totalBayar): string
    {
        if (bccomp($totalBayar, $totalTagihan, 2) >= 0) {
            return 'LUNAS';
        }

        return bccomp($totalBayar, '0.00', 2) === 1 ? 'CICIL' : 'BELUM';
    }

    /**
     * Dipakai untuk KEDUA jenis tagihan — tanda tangannya sekarang
     * menerima union type, bukan cuma TagihanMahasiswa, karena
     * TagihanNonReguler punya kolom mahasiswa_id & kode_transaksi yang
     * sama-sama dipakai di sini.
     */
    private function catatKelebihanKeSaldo(PembayaranMahasiswa $pembayaran, TagihanMahasiswa|TagihanNonReguler $tagihan, string $kelebihan): void
    {
        $saldo = KeuanganSaldo::where('mahasiswa_id', $tagihan->mahasiswa_id)
            ->lockForUpdate()
            ->first();

        if (! $saldo) {
            $saldo = KeuanganSaldo::create([
                'mahasiswa_id' => $tagihan->mahasiswa_id,
                'saldo' => 0,
                'last_updated_at' => now(),
            ]);
        }

        $saldo->saldo = bcadd((string) $saldo->saldo, $kelebihan, 2);
        $saldo->last_updated_at = now();
        $saldo->save();

        KeuanganSaldoTransaction::create([
            'saldo_id' => $saldo->id,
            'tipe' => 'IN',
            'nominal' => $kelebihan,
            'referensi_id' => $pembayaran->id,
            'keterangan' => 'Kelebihan pembayaran dari invoice ' . $tagihan->kode_transaksi,
        ]);
    }

    /**
     * Sekarang perilakunya sama dengan alokasikanTagihanMahasiswa():
     * cap ke sisa tagihan, alokasikan ke detail komponen FIFO berdasar
     * urutan_prioritas, dan kelebihan bayar dicatat ke keuangan_saldos —
     * bukan lagi menambah total_bayar tanpa batas seperti versi stub
     * sebelumnya.
     */
    private function alokasikanTagihanNonReguler(
        PembayaranMahasiswa $pembayaran,
        TagihanNonReguler $tagihan
    ): void {
        $tagihan->refresh();

        $sisaSebelumAlokasi = bcsub(
            (string) $tagihan->total_tagihan,
            (string) $tagihan->total_bayar,
            2
        );

        $nominalBayar = (string) $pembayaran->nominal_bayar;

        $lebihDariSisa = bccomp($nominalBayar, $sisaSebelumAlokasi, 2) === 1;

        $dialokasikanKeTagihan = $lebihDariSisa
            ? $sisaSebelumAlokasi
            : $nominalBayar;

        $kelebihan = $lebihDariSisa
            ? bcsub($nominalBayar, $sisaSebelumAlokasi, 2)
            : '0.00';

        $totalBayarBaru = bcadd(
            (string) $tagihan->total_bayar,
            $dialokasikanKeTagihan,
            2
        );

        $tagihan->total_bayar = $totalBayarBaru;

        $tagihan->status_bayar = $this->tentukanStatusBayar(
            (string) $tagihan->total_tagihan,
            $totalBayarBaru
        );

        $tagihan->save();

        if (bccomp($dialokasikanKeTagihan, '0.00', 2) === 1) {
            $this->alokasikanKeDetailKomponenNonReguler(
                $tagihan->id,
                $dialokasikanKeTagihan
            );
        }

        if (bccomp($kelebihan, '0.00', 2) === 1) {
            $this->catatKelebihanKeSaldo(
                $pembayaran,
                $tagihan,
                $kelebihan
            );
        }
    }
}
