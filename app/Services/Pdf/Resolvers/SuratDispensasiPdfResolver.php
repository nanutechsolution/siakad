<?php

namespace App\Services\Pdf\Resolvers;

use App\Contracts\Pdf\PdfDataResolverInterface;
use App\DataTransferObjects\Pdf\SuratDispensasiPdfData;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SuratDispensasiPdfResolver implements PdfDataResolverInterface
{
    public function resolve(array $context): SuratDispensasiPdfData
    {
        $dispensasiId = $context['dispensasi_id'] ?? throw new RuntimeException('Context [dispensasi_id] wajib diisi.');

        $dispensasi = DB::table('dispensasi_akademiks')
            ->join('mahasiswas', 'mahasiswas.id', '=', 'dispensasi_akademiks.mahasiswa_id')
            ->join('ref_person', 'ref_person.id', '=', 'mahasiswas.person_id')
            ->join('ref_prodi', 'ref_prodi.id', '=', 'mahasiswas.prodi_id')
            ->where('dispensasi_akademiks.id', $dispensasiId)
            ->select([
                'dispensasi_akademiks.id',
                'dispensasi_akademiks.jenis',
                'dispensasi_akademiks.alasan',
                'dispensasi_akademiks.berlaku_mulai',
                'dispensasi_akademiks.berlaku_sampai',
                'dispensasi_akademiks.status',
                'dispensasi_akademiks.updated_at',
                'mahasiswas.nim',
                'ref_person.nama_lengkap as nama_mahasiswa',
                'ref_prodi.id as prodi_id_val',
                'ref_prodi.nama_prodi',
            ])
            ->first();

        if (! $dispensasi) {
            throw new RuntimeException("Dispensasi dengan id [{$dispensasiId}] tidak ditemukan.");
        }

        if ($dispensasi->status !== 'AKTIF') {
            throw new RuntimeException(
                'Surat Keterangan Dispensasi hanya dapat diterbitkan untuk dispensasi berstatus AKTIF. Status saat ini: ' . $dispensasi->status
            );
        }

        return new SuratDispensasiPdfData(
            dispensasiId: $dispensasi->id,
            prodiId: (int) $dispensasi->prodi_id_val,
            nim: $dispensasi->nim,
            namaMahasiswa: $dispensasi->nama_mahasiswa,
            namaProdi: $dispensasi->nama_prodi,
            jenisDispensasi: $dispensasi->jenis,
            alasan: $dispensasi->alasan,
            berlakuMulai: $dispensasi->berlaku_mulai,
            berlakuSampai: $dispensasi->berlaku_sampai,
            dicetakPada: now()->translatedFormat('d F Y H:i'),
            sourceUpdatedAt: (string) $dispensasi->updated_at,
        );
    }
}
