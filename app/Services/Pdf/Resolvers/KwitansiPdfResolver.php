<?php

namespace App\Services\Pdf\Resolvers;

use App\Contracts\Pdf\PdfDataResolverInterface;
use App\DataTransferObjects\Pdf\KwitansiPdfData;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class KwitansiPdfResolver implements PdfDataResolverInterface
{
    public function resolve(array $context): KwitansiPdfData
    {
        $pembayaranId = $context['pembayaran_id'] ?? throw new RuntimeException('Context [pembayaran_id] wajib diisi.');

        $pembayaran = DB::table('pembayaran_mahasiswas')->where('id', $pembayaranId)->first();

        if (! $pembayaran) {
            throw new RuntimeException("Pembayaran dengan id [{$pembayaranId}] tidak ditemukan.");
        }

        $statusVerifikasi = DB::table('ref_status_verifikasi_pembayaran')
            ->where('id', $pembayaran->status_verifikasi_id)
            ->first();

        $kodeTerverifikasi = config('pdf.kode_status_verifikasi_terverifikasi', 'VERIFIED');

        if (! $statusVerifikasi || strtoupper($statusVerifikasi->kode) !== strtoupper($kodeTerverifikasi)) {
            throw new RuntimeException(
                'Kwitansi hanya dapat dicetak untuk pembayaran berstatus TERVERIFIKASI. Status saat ini: ' .
                    ($statusVerifikasi->nama ?? 'tidak diketahui')
            );
        }

        $tagihanTypeReguler = config('pdf.tagihan_type_reguler', 'tagihan_mahasiswa');

        if ($pembayaran->tagihan_type !== $tagihanTypeReguler) {
            throw new RuntimeException(
                'Resolver Kwitansi Fase 2 baru mendukung tagihan reguler (tagihan_mahasiswa). Tagihan non-reguler menyusul.'
            );
        }

        $tagihan = DB::table('tagihan_mahasiswas')
            ->join('mahasiswas', 'mahasiswas.id', '=', 'tagihan_mahasiswas.mahasiswa_id')
            ->join('ref_person', 'ref_person.id', '=', 'mahasiswas.person_id')
            ->join('ref_prodi', 'ref_prodi.id', '=', 'mahasiswas.prodi_id')
            ->join('ref_tahun_akademik', 'ref_tahun_akademik.id', '=', 'tagihan_mahasiswas.tahun_akademik_id')
            ->where('tagihan_mahasiswas.id', $pembayaran->tagihan_id)
            ->select([
                'tagihan_mahasiswas.deskripsi',
                'mahasiswas.nim',
                'ref_person.nama_lengkap as nama_mahasiswa',
                'ref_prodi.id as prodi_id_val',
                'ref_prodi.nama_prodi',
                'ref_tahun_akademik.nama_tahun',
            ])
            ->first();

        if (! $tagihan) {
            throw new RuntimeException('Tagihan terkait pembayaran ini tidak ditemukan.');
        }

        return new KwitansiPdfData(
            pembayaranId: $pembayaran->id,
            prodiId: (int) $tagihan->prodi_id_val,
            nim: $tagihan->nim,
            namaMahasiswa: $tagihan->nama_mahasiswa,
            namaProdi: $tagihan->nama_prodi,
            namaTahunAkademik: $tagihan->nama_tahun,
            namaTagihan: $tagihan->deskripsi,
            nominalBayar: (float) $pembayaran->nominal_bayar,
            metodePembayaran: $pembayaran->metode_pembayaran,
            tanggalBayar: $pembayaran->tanggal_bayar,
            keteranganPengirim: $pembayaran->keterangan_pengirim,
            dicetakPada: now()->translatedFormat('d F Y H:i'),
        );
    }
}
