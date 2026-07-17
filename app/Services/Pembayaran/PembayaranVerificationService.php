<?php

namespace App\Services\Pembayaran;

use App\Enums\StatusVerifikasiPembayaran;
use App\Events\PembayaranTerverifikasi;
use App\Exceptions\Pembayaran\PembayaranSudahDiprosesException;
use App\Models\PembayaranMahasiswa;
use App\Services\Keuangan\LedgerService;
use Illuminate\Support\Facades\DB;

class PembayaranVerificationService
{
    public function __construct(
        private readonly PembayaranAllocationService $allocationService,
        private readonly LedgerService $ledger,
    ) {}

    public function verifikasi(PembayaranMahasiswa $pembayaran, string $verifikatorUserId): PembayaranMahasiswa
    {
        return DB::transaction(function () use ($pembayaran, $verifikatorUserId) {
            $pembayaran = PembayaranMahasiswa::whereKey($pembayaran->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $this->pastikanMasihPending($pembayaran);

            $pembayaran->status_verifikasi_id = StatusVerifikasiPembayaran::VERIFIED;
            $pembayaran->verified_by = $verifikatorUserId;
            $pembayaran->verified_at = now();
            $pembayaran->save();

            $this->allocationService->alokasikan($pembayaran);

            $pembayaran->refresh();
            $pembayaran->loadMissing('tagihan');
            // Catat ke buku besar SETELAH alokasi berhasil (bukan sebelumnya),
            // supaya kalau alokasi gagal (mis. jenis tagihan tidak dikenali),
            // seluruh transaksi rollback termasuk yang di sini — tidak ada
            // entri PEMBAYARAN yang "menggantung" tanpa alokasi nyata.
            //
            // Nominal yang dicatat = nominal_bayar PENUH, bukan cuma bagian
            // yang terserap ke tagihan. Kalau ada kelebihan bayar, kelebihan
            // itu tetap "uang yang diterima dari mahasiswa" — piutang tetap
            // berkurang sejumlah itu, dan saldo_berjalan yang jadi negatif
            // justru benar secara akuntansi (artinya kampus sekarang punya
            // kewajiban saldo/kredit ke mahasiswa, cerminan dari
            // keuangan_saldos yang diisi PembayaranAllocationService).
            $tagihan = $pembayaran->tagihan;

            $this->ledger->recordPembayaran(
                mahasiswaId: $tagihan->mahasiswa_id,
                nominal: (string) $pembayaran->nominal_bayar,
                referensiDokumen: "pembayaran:{$pembayaran->id}",
                keterangan: "Pembayaran diverifikasi untuk {$tagihan->kode_transaksi}",
            );

            event(new PembayaranTerverifikasi($pembayaran));

            return $pembayaran;
        });
    }

    public function tolak(string $pembayaranId, string $verifikatorUserId, string $catatan): PembayaranMahasiswa
    {
        return DB::transaction(function () use ($pembayaranId, $verifikatorUserId, $catatan) {
            $pembayaran = PembayaranMahasiswa::whereKey($pembayaranId)
                ->lockForUpdate()
                ->firstOrFail();

            $this->pastikanMasihPending($pembayaran);

            $pembayaran->status_verifikasi_id = StatusVerifikasiPembayaran::REJECTED;
            $pembayaran->verified_by = $verifikatorUserId;
            $pembayaran->verified_at = now();
            $pembayaran->catatan_verifikasi = $catatan;
            $pembayaran->save();

            return $pembayaran;
        });
    }

    private function pastikanMasihPending(PembayaranMahasiswa $pembayaran): void
    {

        if ($pembayaran->status_verifikasi_id !== StatusVerifikasiPembayaran::PENDING) {
            throw new PembayaranSudahDiprosesException($pembayaran);
        }
    }
}
