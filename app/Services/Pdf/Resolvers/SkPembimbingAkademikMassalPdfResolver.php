<?php

namespace App\Services\Pdf\Resolvers;

use App\Contracts\Pdf\PdfDataResolverInterface;
use App\DataTransferObjects\Pdf\SkPembimbingAkademikMassalPdfData;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SkPembimbingAkademikMassalPdfResolver implements PdfDataResolverInterface
{
    public function resolve(array $context): SkPembimbingAkademikMassalPdfData
    {
        $dosenId = $context['dosen_id']
            ?? throw new RuntimeException(
                'Context [dosen_id] wajib diisi.'
            );

        /*
         * ============================================================
         * 1. DATA DOSEN
         * ============================================================
         *
         * trx_dosen
         *   -> ref_person
         *   -> ref_prodi
         *   -> ref_fakultas
         */
        $dosen = DB::table('trx_dosen')
            ->join(
                'ref_person',
                'ref_person.id',
                '=',
                'trx_dosen.person_id'
            )
            ->join(
                'ref_prodi',
                'ref_prodi.id',
                '=',
                'trx_dosen.prodi_id'
            )
            ->join(
                'ref_fakultas',
                'ref_fakultas.id',
                '=',
                'ref_prodi.fakultas_id'
            )
            ->where('trx_dosen.id', $dosenId)
            ->where('trx_dosen.is_active', true)
            ->select([
                'trx_dosen.id as dosen_id',
                'trx_dosen.person_id',
                'trx_dosen.prodi_id',

                'trx_dosen.nidn',
                'trx_dosen.nuptk',
                'trx_dosen.jenis_dosen',

                'ref_person.nama_lengkap as nama_dosen',

                'ref_prodi.nama_prodi',
                'ref_prodi.jenjang',

                'ref_fakultas.id as fakultas_id',
                'ref_fakultas.nama_fakultas',
            ])
            ->first();

        if (! $dosen) {
            throw new RuntimeException(
                "Dosen dengan id [{$dosenId}] tidak ditemukan atau tidak aktif."
            );
        }

        /*
         * ============================================================
         * 2. AMBIL SEMUA PENUGASAN AKTIF DOSEN
         * ============================================================
         */
        $assignments = DB::table('pembimbing_akademik as pa')
            ->join(
                'ref_tahun_akademik as semester_mulai',
                'semester_mulai.id',
                '=',
                'pa.semester_mulai_id'
            )
            ->leftJoin(
                'ref_tahun_akademik as semester_selesai',
                'semester_selesai.id',
                '=',
                'pa.semester_selesai_id'
            )
            ->where('pa.dosen_id', $dosenId)
            ->where('pa.status', 'AKTIF')
            ->whereNull('pa.deleted_at')
            ->select([
                'pa.id',
                'pa.kelas_id',
                'pa.mahasiswa_id',
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

                'pa.created_at',
                'pa.updated_at',

                'semester_mulai.nama_tahun as nama_semester_mulai',
                'semester_mulai.semester as semester_mulai',

                'semester_selesai.nama_tahun as nama_semester_selesai',
                'semester_selesai.semester as semester_selesai',
            ])
            ->orderBy('pa.jenis')
            ->orderBy('pa.semester_mulai_id')
            ->orderBy('pa.id')
            ->get();

        if ($assignments->isEmpty()) {
            throw new RuntimeException(
                "Dosen [{$dosen->nama_dosen}] tidak memiliki penugasan pembimbing akademik aktif."
            );
        }

        /*
         * ============================================================
         * 3. RESOLVE MAHASISWA SETIAP PENUGASAN
         * ============================================================
         *
         * Ada dua kemungkinan:
         *
         * A. DOSEN_WALI + kelas_id
         *    -> ambil semua mahasiswa dari mahasiswa_kelas
         *
         * B. PER MAHASISWA
         *    -> ambil mahasiswa_id langsung
         */
        $assignmentData = $assignments
            ->map(function ($assignment) {

                $mahasiswa = collect();

                /*
                 * ----------------------------------------------------
                 * CASE 1: PENUGASAN PER KELAS
                 * ----------------------------------------------------
                 */
                if ($assignment->kelas_id !== null) {

                    $mahasiswa = DB::table('mahasiswa_kelas as mk')
                        ->join(
                            'mahasiswas as m',
                            'm.id',
                            '=',
                            'mk.mahasiswa_id'
                        )
                        ->join(
                            'ref_person as p',
                            'p.id',
                            '=',
                            'm.person_id'
                        )
                        ->where('mk.kelas_id', $assignment->kelas_id)
                        ->where(function ($query) {
                            $query
                                ->whereNull('mk.tanggal_keluar')
                                ->orWhere(
                                    'mk.tanggal_keluar',
                                    '>=',
                                    $this->today()
                                );
                        })
                        ->select([
                            'm.id as mahasiswa_id',
                            'm.nim',
                            'p.nama_lengkap as nama_mahasiswa',
                        ])
                        ->orderBy('m.nim')
                        ->get();
                }

                /*
                 * ----------------------------------------------------
                 * CASE 2: PENUGASAN PER MAHASISWA
                 * ----------------------------------------------------
                 */ elseif ($assignment->mahasiswa_id !== null) {

                    $mahasiswa = DB::table('mahasiswas as m')
                        ->join(
                            'ref_person as p',
                            'p.id',
                            '=',
                            'm.person_id'
                        )
                        ->where('m.id', $assignment->mahasiswa_id)
                        ->select([
                            'm.id as mahasiswa_id',
                            'm.nim',
                            'p.nama_lengkap as nama_mahasiswa',
                        ])
                        ->get();
                }

                return [
                    'id' => (int) $assignment->id,

                    'jenis' => $assignment->jenis,
                    'isPrimary' => (bool) $assignment->is_primary,

                    'kelasId' => $assignment->kelas_id
                        ? (int) $assignment->kelas_id
                        : null,

                    'mahasiswaId' => $assignment->mahasiswa_id,

                    'semesterMulaiId' => (int) $assignment->semester_mulai_id,
                    'semesterSelesaiId' => $assignment->semester_selesai_id
                        ? (int) $assignment->semester_selesai_id
                        : null,

                    'namaSemesterMulai' => $assignment->nama_semester_mulai,
                    'semesterMulai' => (int) $assignment->semester_mulai,

                    'namaSemesterSelesai' => $assignment->nama_semester_selesai,
                    'semesterSelesai' => $assignment->semester_selesai !== null
                        ? (int) $assignment->semester_selesai
                        : null,

                    'tanggalMulai' => $assignment->tanggal_mulai,
                    'tanggalSelesai' => $assignment->tanggal_selesai,

                    'nomorSk' => $assignment->nomor_sk,
                    'tanggalSk' => $assignment->tanggal_sk,

                    'alasan' => $assignment->alasan,
                    'keterangan' => $assignment->keterangan,

                    'status' => $assignment->status,

                    'mahasiswa' => $mahasiswa
                        ->map(fn($m) => [
                            'id' => $m->mahasiswa_id,
                            'nim' => $m->nim,
                            'nama' => $m->nama_mahasiswa,
                        ])
                        ->values()
                        ->all(),

                    'jumlahMahasiswa' => $mahasiswa->count(),
                ];
            })
            ->values()
            ->all();

        /*
         * ============================================================
         * 4. RETURN DTO
         * ============================================================
         */
        return new SkPembimbingAkademikMassalPdfData(
            dosenId: $dosen->dosen_id,
            personId: (int) $dosen->person_id,

            namaDosen: $dosen->nama_dosen,
            nidn: $dosen->nidn,
            nuptk: $dosen->nuptk,
            jenisDosen: $dosen->jenis_dosen,

            prodiId: (int) $dosen->prodi_id,
            namaProdi: $dosen->nama_prodi,
            jenjang: $dosen->jenjang,

            fakultasId: (int) $dosen->fakultas_id,
            namaFakultas: $dosen->nama_fakultas,

            assignments: $assignmentData,

            jumlahPenugasan: count($assignmentData),

            dicetakPada: now()->translatedFormat('d F Y H:i'),

            sourceUpdatedAt: (string) (
                $assignments
                ->max('updated_at')
                ?? now()
            ),
        );
    }

    protected function today(): string
    {
        return now()->toDateString();
    }
}
