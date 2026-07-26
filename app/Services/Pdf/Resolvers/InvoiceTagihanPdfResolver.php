<?php

namespace App\Services\Pdf\Resolvers;

use App\Contracts\Pdf\PdfDataResolverInterface;
use App\DataTransferObjects\Pdf\InvoiceTagihanPdfData;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InvoiceTagihanPdfResolver implements PdfDataResolverInterface
{
    public function resolve(array $context): InvoiceTagihanPdfData
    {
        $tagihanId = $context['tagihan_id'] ?? throw new RuntimeException('Context [tagihan_id] wajib diisi.');

        $tagihan = DB::table('tagihan_mahasiswas')
            ->join('mahasiswas', 'mahasiswas.id', '=', 'tagihan_mahasiswas.mahasiswa_id')
            ->join('ref_person', 'ref_person.id', '=', 'mahasiswas.person_id')
            ->join('ref_prodi', 'ref_prodi.id', '=', 'mahasiswas.prodi_id')
            ->join('ref_tahun_akademik', 'ref_tahun_akademik.id', '=', 'tagihan_mahasiswas.tahun_akademik_id')
            ->where('tagihan_mahasiswas.id', $tagihanId)
            ->select([
                'tagihan_mahasiswas.id',
                'tagihan_mahasiswas.kode_transaksi',
                'tagihan_mahasiswas.deskripsi',
                'tagihan_mahasiswas.total_tagihan',
                'tagihan_mahasiswas.total_bayar',
                'tagihan_mahasiswas.sisa_tagihan',
                'tagihan_mahasiswas.status_bayar',
                'tagihan_mahasiswas.tenggat_waktu',
                'tagihan_mahasiswas.updated_at',
                'mahasiswas.nim',
                'ref_person.nama_lengkap as nama_mahasiswa',
                'ref_prodi.nama_prodi',
                'ref_tahun_akademik.nama_tahun',
            ])
            ->first();

        if (! $tagihan) {
            throw new RuntimeException("Tagihan dengan id [{$tagihanId}] tidak ditemukan.");
        }

        $details = DB::table('tagihan_mahasiswas_details')
            ->where('tagihan_id', $tagihanId)
            ->orderBy('id')
            ->select(['nama_komponen_snapshot', 'nominal_dasar', 'nominal_diskon', 'nominal_tagihan', 'updated_at'])
            ->get();

        $items = $details->map(fn($row) => [
            'namaKomponen' => $row->nama_komponen_snapshot,
            'nominalDasar' => (float) $row->nominal_dasar,
            'nominalDiskon' => (float) $row->nominal_diskon,
            'nominalTagihan' => (float) $row->nominal_tagihan,
        ])->values()->all();

        $sourceUpdatedAt = (string) max($tagihan->updated_at, $details->max('updated_at') ?? $tagihan->updated_at);

        return new InvoiceTagihanPdfData(
            tagihanId: $tagihan->id,
            kodeTransaksi: $tagihan->kode_transaksi,
            nim: $tagihan->nim,
            namaMahasiswa: $tagihan->nama_mahasiswa,
            namaProdi: $tagihan->nama_prodi,
            namaTahunAkademik: $tagihan->nama_tahun,
            deskripsi: $tagihan->deskripsi,
            totalTagihan: (float) $tagihan->total_tagihan,
            totalBayar: (float) $tagihan->total_bayar,
            sisaTagihan: (float) $tagihan->sisa_tagihan,
            statusBayar: $tagihan->status_bayar,
            tenggatWaktu: $tagihan->tenggat_waktu,
            items: $items,
            dicetakPada: now()->translatedFormat('d F Y H:i'),
            sourceUpdatedAt: $sourceUpdatedAt,
        );
    }
}
