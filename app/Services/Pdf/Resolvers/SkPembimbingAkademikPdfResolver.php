<?php

namespace App\Services\Pdf\Resolvers;

use App\Contracts\Pdf\PdfDataResolverInterface;
use App\DataTransferObjects\Pdf\SkPembimbingAkademikPdfData;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SkPembimbingAkademikPdfResolver implements PdfDataResolverInterface
{
    public function resolve(array $context): SkPembimbingAkademikPdfData
    {
        $id = $context['pembimbing_akademik_id']
            ?? throw new RuntimeException(
                'Context [pembimbing_akademik_id] wajib diisi.'
            );

        $data = DB::table('pembimbing_akademik as pa')

            // Mahasiswa
            ->leftJoin(
                'mahasiswas as m',
                'm.id',
                '=',
                'pa.mahasiswa_id'
            )

            // Person mahasiswa
            ->leftJoin(
                'ref_person as mahasiswa_person',
                'mahasiswa_person.id',
                '=',
                'm.person_id'
            )

            // Prodi mahasiswa
            ->leftJoin(
                'ref_prodi as prodi',
                'prodi.id',
                '=',
                'm.prodi_id'
            )

            // Fakultas mahasiswa
            ->leftJoin(
                'ref_fakultas as fakultas',
                'fakultas.id',
                '=',
                'prodi.fakultas_id'
            )

            // Dosen pembimbing
            ->join(
                'trx_dosen as dosen',
                'dosen.id',
                '=',
                'pa.dosen_id'
            )

            // Person dosen
            ->join(
                'ref_person as dosen_person',
                'dosen_person.id',
                '=',
                'dosen.person_id'
            )

            // Semester mulai
            ->join(
                'ref_tahun_akademik as semester_mulai',
                'semester_mulai.id',
                '=',
                'pa.semester_mulai_id'
            )

            ->where('pa.id', $id)

            ->select([
                'pa.id',
                'pa.mahasiswa_id',
                'pa.dosen_id',
                'pa.jenis',
                'pa.is_primary',
                'pa.semester_mulai_id',
                'pa.semester_selesai_id',
                'pa.tanggal_mulai',
                'pa.tanggal_selesai',
                'pa.nomor_sk',
                'pa.tanggal_sk',
                'pa.alasan',
                'pa.keterangan',
                'pa.status',
                'pa.updated_at',

                // Mahasiswa
                'm.nim',
                'mahasiswa_person.nama_lengkap as nama_mahasiswa',

                // Dosen
                'dosen.person_id as dosen_person_id',
                'dosen.nidn',
                'dosen.nuptk',
                'dosen_person.nama_lengkap as nama_dosen',

                // Akademik
                'prodi.id as prodi_id',
                'prodi.nama_prodi',
                'prodi.jenjang',
                'fakultas.id as fakultas_id',
                'fakultas.nama_fakultas',

                // Semester
                'semester_mulai.nama_tahun as nama_tahun_akademik',
                'semester_mulai.semester',
            ])

            ->first();

        if (! $data) {
            throw new RuntimeException(
                "Data Pembimbing Akademik dengan id [{$id}] tidak ditemukan."
            );
        }

        return new SkPembimbingAkademikPdfData(
            pembimbingAkademikId: (int) $data->id,

            personId: (int) $data->dosen_person_id,

            namaPembimbing: (string) $data->nama_dosen,

            nipPembimbing: (string) (
                $data->nidn
                ?? $data->nuptk
                ?? '-'
            ),

            jabatanPembimbing: 'Dosen Pembimbing Akademik',

            prodiId: $data->prodi_id
                ? (int) $data->prodi_id
                : null,

            namaProdi: (string) ($data->nama_prodi ?? '-'),

            namaFakultas: (string) ($data->nama_fakultas ?? '-'),

            jumlahMahasiswa: $this->jumlahMahasiswa(
                $data
            ),

            tahunAkademik: (string) $data->nama_tahun_akademik,

            dicetakPada: now()->translatedFormat('d F Y H:i'),

            sourceUpdatedAt: (string) $data->updated_at,

            fakultasId: $data->fakultas_id
                ? (int) $data->fakultas_id
                : null,

            nim: $data->nim,

            namaMahasiswa: $data->nama_mahasiswa,

            jenis: $data->jenis,

            nomorSk: $data->nomor_sk,

            tanggalSk: $data->tanggal_sk,
        );
    }

    protected function jumlahMahasiswa(object $data): int
    {
        /*
         * Kalau record ini PER MAHASISWA:
         * jumlahnya 1.
         *
         * Kalau nanti SK dibuat per KELAS,
         * logic ini bisa diganti COUNT mahasiswa_kelas.
         */
        return $data->mahasiswa_id ? 1 : 0;
    }
}
