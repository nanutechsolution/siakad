<?php

namespace App\Services\Pdf\Resolvers;

use App\Contracts\Pdf\PdfDataResolverInterface;
use App\DataTransferObjects\Pdf\SuratAktifKuliahPdfData;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SuratAktifKuliahPdfResolver implements PdfDataResolverInterface
{
    public function resolve(array $context): SuratAktifKuliahPdfData
    {
        $riwayatId = $context['riwayat_status_id'] ?? throw new RuntimeException('Context [riwayat_status_id] wajib diisi.');

        $riwayat = DB::table('riwayat_status_mahasiswas')
            ->join('mahasiswas', 'mahasiswas.id', '=', 'riwayat_status_mahasiswas.mahasiswa_id')
            ->join('ref_person', 'ref_person.id', '=', 'mahasiswas.person_id')
            ->join('ref_prodi', 'ref_prodi.id', '=', 'mahasiswas.prodi_id')
            ->join('ref_fakultas', 'ref_fakultas.id', '=', 'ref_prodi.fakultas_id')
            ->join('ref_tahun_akademik', 'ref_tahun_akademik.id', '=', 'riwayat_status_mahasiswas.tahun_akademik_id')
            ->where('riwayat_status_mahasiswas.id', $riwayatId)
            ->select([
                'riwayat_status_mahasiswas.id',
                'riwayat_status_mahasiswas.status_kuliah',
                'riwayat_status_mahasiswas.updated_at',
                'mahasiswas.nim',
                'ref_person.nama_lengkap as nama_mahasiswa',
                'ref_person.tempat_lahir',
                'ref_person.tanggal_lahir',
                'ref_prodi.nama_prodi',
                'ref_prodi.jenjang',
                'ref_fakultas.nama_fakultas',
                'ref_tahun_akademik.nama_tahun',
                'ref_tahun_akademik.semester',
            ])
            ->first();

        if (! $riwayat) {
            throw new RuntimeException("Data status mahasiswa dengan id [{$riwayatId}] tidak ditemukan.");
        }

        $kodeAktif = config('pdf.kode_status_kuliah_aktif', 'A');

        if ($riwayat->status_kuliah !== $kodeAktif) {
            throw new RuntimeException(
                'Surat Keterangan Aktif Kuliah hanya dapat diterbitkan untuk mahasiswa berstatus AKTIF pada semester tersebut.'
            );
        }

        return new SuratAktifKuliahPdfData(
            riwayatStatusId: (int) $riwayat->id,
            nim: $riwayat->nim,
            namaMahasiswa: $riwayat->nama_mahasiswa,
            tempatLahir: $riwayat->tempat_lahir,
            tanggalLahir: $riwayat->tanggal_lahir,
            namaProdi: $riwayat->nama_prodi,
            jenjang: $riwayat->jenjang,
            namaFakultas: $riwayat->nama_fakultas,
            namaTahunAkademik: $riwayat->nama_tahun,
            semester: (int) $riwayat->semester,
            dicetakPada: now()->translatedFormat('d F Y H:i'),
            sourceUpdatedAt: (string) $riwayat->updated_at,
        );
    }
}
