<?php

namespace App\Services\Pdf\Resolvers;

use App\Contracts\Pdf\PdfDataResolverInterface;
use App\DataTransferObjects\Pdf\KrsPdfData;
use App\Models\Mahasiswa;
use App\Services\Akademik\PembimbingAkademikResolver;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class KrsPdfResolver implements PdfDataResolverInterface
{
    public function resolve(array $context): KrsPdfData
    {
        $krsId = $context['krs_id'] ?? throw new RuntimeException('Context [krs_id] wajib diisi untuk mencetak KRS.');

        $krs = DB::table('krs')
            ->join('mahasiswas', 'mahasiswas.id', '=', 'krs.mahasiswa_id')
            ->join('ref_person', 'ref_person.id', '=', 'mahasiswas.person_id')
            ->join('ref_prodi', 'ref_prodi.id', '=', 'mahasiswas.prodi_id')
            ->join('ref_fakultas', 'ref_fakultas.id', '=', 'ref_prodi.fakultas_id')
            ->join('ref_tahun_akademik', 'ref_tahun_akademik.id', '=', 'krs.tahun_akademik_id')
            ->where('krs.id', $krsId)
            ->select([
                'krs.id as krs_id',
                'krs.mahasiswa_id',
                'krs.status_krs',
                'krs.total_sks_diambil',
                'krs.disetujui_pada',
                'mahasiswas.nim',
                'ref_person.nama_lengkap as nama_mahasiswa',
                'ref_prodi.nama_prodi',
                'ref_prodi.id as prodi_id',
                'ref_prodi.jenjang',
                'ref_fakultas.nama_fakultas',
                'ref_fakultas.id as fakultas_id',
                'ref_tahun_akademik.nama_tahun',
                'ref_tahun_akademik.semester',
            ])
            ->first();

        if (! $krs) {
            throw new RuntimeException("KRS dengan id [{$krsId}] tidak ditemukan.");
        }

        // Dosen wali diambil dari resolver yang sama dengan halaman akademik.
        // Relasi dimuat lengkap agar nama memakai gelar dan NIDN tidak hilang.
        $dosenWali = null;

        $mahasiswa = Mahasiswa::find($krs->mahasiswa_id);

        if ($mahasiswa) {
            $pembimbing = app(PembimbingAkademikResolver::class)
                ->dosenWaliAktif($mahasiswa);

            if ($pembimbing) {
                $pembimbing->loadMissing(['dosen.person.gelars']);
                $dosenWali = $pembimbing->dosen;
            }
        }
        $kelasId = DB::table('mahasiswa_kelas')
            ->where('mahasiswa_id', $krs->mahasiswa_id)
            ->orderBy('id')
            ->value('kelas_id');


        $details = DB::table('krs_detail')
            ->where('krs_id', $krsId)
            ->orderBy('id')
            ->select([
                'id',
                'jadwal_kuliah_id',
                'kode_mk_snapshot',
                'nama_mk_snapshot',
                'sks_snapshot',
                'activity_type_snapshot',
                'status_ambil',
            ])
            ->get();

        $jadwalIds = $details->pluck('jadwal_kuliah_id')->filter()->unique()->values();

        $jadwalInfo = DB::table('jadwal_kuliah')
            ->leftJoin('kelas', 'kelas.id', '=', 'jadwal_kuliah.kelas_id')
            ->leftJoin('ref_ruang', 'ref_ruang.id', '=', 'jadwal_kuliah.ruang_id')
            ->whereIn('jadwal_kuliah.id', $jadwalIds)
            ->select([
                'jadwal_kuliah.id',
                'jadwal_kuliah.hari',
                'jadwal_kuliah.jam_mulai',
                'jadwal_kuliah.jam_selesai',
                'kelas.nama_kelas',
                'ref_ruang.nama_ruang',
            ])
            ->get()
            ->keyBy('id');

        $dosenPengampu = DB::table('jadwal_kuliah_dosen')
            ->join('trx_dosen', 'trx_dosen.id', '=', 'jadwal_kuliah_dosen.dosen_id')
            ->join('ref_person', 'ref_person.id', '=', 'trx_dosen.person_id')
            ->whereIn('jadwal_kuliah_dosen.jadwal_kuliah_id', $jadwalIds)
            ->orderByDesc('jadwal_kuliah_dosen.is_koordinator')
            ->select(['jadwal_kuliah_dosen.jadwal_kuliah_id', 'ref_person.nama_lengkap'])
            ->get()
            ->groupBy('jadwal_kuliah_id')
            ->map(fn($rows) => $rows->pluck('nama_lengkap')->implode(', '));

        $items = $details->map(function ($detail) use ($jadwalInfo, $dosenPengampu) {
            $jadwal = $jadwalInfo->get($detail->jadwal_kuliah_id);

            return [
                'kodeMk' => $detail->kode_mk_snapshot,
                'namaMk' => $detail->nama_mk_snapshot,
                'sks' => $detail->sks_snapshot,
                'jenisAktivitas' => $detail->activity_type_snapshot,
                'statusAmbil' => $detail->status_ambil,
                'kelas' => $jadwal->nama_kelas ?? '-',
                'jadwal' => $jadwal
                    ? ucfirst(strtolower($jadwal->hari)) . ' ' . substr($jadwal->jam_mulai, 0, 5) . '-' . substr($jadwal->jam_selesai, 0, 5)
                    : '-',
                'ruang' => $jadwal->nama_ruang ?? '-',
                'dosen' => $dosenPengampu->get($detail->jadwal_kuliah_id, '-'),
            ];
        })->values()->all();

        return new KrsPdfData(
            krsId: $krs->krs_id,
            nim: $krs->nim,
            namaMahasiswa: $krs->nama_mahasiswa,
            namaProdi: $krs->nama_prodi,
            namaFakultas: $krs->nama_fakultas,
            jenjang: $krs->jenjang,
            namaTahunAkademik: $krs->nama_tahun,
            semester: (int) $krs->semester,
            namaDosenWali: $dosenWali?->person?->nama_dengan_gelar,
            nidnDosenWali: $dosenWali?->nidn,
            statusKrs: $krs->status_krs,
            totalSks: (int) $krs->total_sks_diambil,
            disetujuiPada: $krs->disetujui_pada,
            items: $items,
            dicetakPada: now()->translatedFormat('d F Y H:i'),
            prodiId: (int) $krs->prodi_id,
            fakultasId: (int) $krs->fakultas_id,
        );
    }
}
