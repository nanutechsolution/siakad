<?php

namespace App\Services\Pdf\Resolvers;

use App\Contracts\Pdf\PdfDataResolverInterface;
use App\DataTransferObjects\Pdf\SuratPindahProdiPdfData;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SuratPindahProdiPdfResolver implements PdfDataResolverInterface
{
    public function resolve(array $context): SuratPindahProdiPdfData
    {
        $riwayatProdiId = $context['riwayat_prodi_id'] ?? throw new RuntimeException('Context [riwayat_prodi_id] wajib diisi.');

        $riwayat = DB::table('riwayat_prodi_mahasiswas')
            ->join('mahasiswas', 'mahasiswas.id', '=', 'riwayat_prodi_mahasiswas.mahasiswa_id')
            ->join('ref_person', 'ref_person.id', '=', 'mahasiswas.person_id')
            ->join('ref_prodi', 'ref_prodi.id', '=', 'riwayat_prodi_mahasiswas.prodi_id')
            ->where('riwayat_prodi_mahasiswas.id', $riwayatProdiId)
            ->select([
                'riwayat_prodi_mahasiswas.id',
                'riwayat_prodi_mahasiswas.tanggal_berlaku',
                'riwayat_prodi_mahasiswas.updated_at',
                'mahasiswas.id as mahasiswa_id',
                'mahasiswas.nim',
                'ref_person.nama_lengkap as nama_mahasiswa',
                'ref_prodi.nama_prodi as prodi_tujuan',
            ])
            ->first();

        if (! $riwayat) {
            throw new RuntimeException("Riwayat pindah prodi dengan id [{$riwayatProdiId}] tidak ditemukan.");
        }

        $prodiAsal = DB::table('riwayat_prodi_mahasiswas')
            ->join('ref_prodi', 'ref_prodi.id', '=', 'riwayat_prodi_mahasiswas.prodi_id')
            ->where('riwayat_prodi_mahasiswas.mahasiswa_id', $riwayat->mahasiswa_id)
            ->where('riwayat_prodi_mahasiswas.tanggal_berlaku', '<', $riwayat->tanggal_berlaku)
            ->orderByDesc('riwayat_prodi_mahasiswas.tanggal_berlaku')
            ->value('ref_prodi.nama_prodi');

        if (! $prodiAsal) {
            throw new RuntimeException(
                'Tidak ditemukan riwayat prodi sebelumnya — data ini tampak sebagai prodi awal mahasiswa, bukan hasil perpindahan.'
            );
        }

        return new SuratPindahProdiPdfData(
            riwayatProdiId: (int) $riwayat->id,
            nim: $riwayat->nim,
            namaMahasiswa: $riwayat->nama_mahasiswa,
            prodiAsal: $prodiAsal,
            prodiTujuan: $riwayat->prodi_tujuan,
            tanggalBerlaku: $riwayat->tanggal_berlaku,
            dicetakPada: now()->translatedFormat('d F Y H:i'),
            sourceUpdatedAt: (string) $riwayat->updated_at,
        );
    }
}
