<?php

namespace App\Services\Pdf\Resolvers;

use App\Contracts\Pdf\PdfDataResolverInterface;
use App\DataTransferObjects\Pdf\JadwalKuliahPdfData;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class JadwalKuliahPdfResolver implements PdfDataResolverInterface
{
    public function resolve(array $context): JadwalKuliahPdfData
    {
        $mahasiswaId = $context['mahasiswa_id'] ?? throw new RuntimeException('Context [mahasiswa_id] wajib diisi.');
        $tahunAkademikId = $context['tahun_akademik_id'] ?? throw new RuntimeException('Context [tahun_akademik_id] wajib diisi.');

        $mahasiswa = DB::table('mahasiswas')
            ->join('ref_person', 'ref_person.id', '=', 'mahasiswas.person_id')
            ->join('ref_prodi', 'ref_prodi.id', '=', 'mahasiswas.prodi_id')
            ->where('mahasiswas.id', $mahasiswaId)
            ->select(['mahasiswas.nim', 'ref_person.nama_lengkap as nama_mahasiswa', 'ref_prodi.nama_prodi'])
            ->first();

        if (! $mahasiswa) {
            throw new RuntimeException("Mahasiswa dengan id [{$mahasiswaId}] tidak ditemukan.");
        }

        $tahunAkademik = DB::table('ref_tahun_akademik')->where('id', $tahunAkademikId)->first();

        if (! $tahunAkademik) {
            throw new RuntimeException("Tahun akademik dengan id [{$tahunAkademikId}] tidak ditemukan.");
        }

        $krsId = DB::table('krs')
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('tahun_akademik_id', $tahunAkademikId)
            ->value('id');

        $rows = collect();

        if ($krsId) {
            $rows = DB::table('krs_detail')
                ->join('jadwal_kuliah', 'jadwal_kuliah.id', '=', 'krs_detail.jadwal_kuliah_id')
                ->leftJoin('kelas', 'kelas.id', '=', 'jadwal_kuliah.kelas_id')
                ->leftJoin('ref_ruang', 'ref_ruang.id', '=', 'jadwal_kuliah.ruang_id')
                ->where('krs_detail.krs_id', $krsId)
                ->orderByRaw("FIELD(jadwal_kuliah.hari, 'SENIN','SELASA','RABU','KAMIS','JUMAT','SABTU','MINGGU')")
                ->orderBy('jadwal_kuliah.jam_mulai')
                ->select([
                    'krs_detail.kode_mk_snapshot',
                    'krs_detail.nama_mk_snapshot',
                    'krs_detail.sks_snapshot',
                    'jadwal_kuliah.id as jadwal_kuliah_id',
                    'jadwal_kuliah.hari',
                    'jadwal_kuliah.jam_mulai',
                    'jadwal_kuliah.jam_selesai',
                    'kelas.nama_kelas',
                    'ref_ruang.nama_ruang',
                ])
                ->get();
        }

        $jadwalIds = $rows->pluck('jadwal_kuliah_id')->unique()->values();

        $dosenPengampu = DB::table('jadwal_kuliah_dosen')
            ->join('trx_dosen', 'trx_dosen.id', '=', 'jadwal_kuliah_dosen.dosen_id')
            ->join('ref_person', 'ref_person.id', '=', 'trx_dosen.person_id')
            ->whereIn('jadwal_kuliah_dosen.jadwal_kuliah_id', $jadwalIds)
            ->orderByDesc('jadwal_kuliah_dosen.is_koordinator')
            ->select(['jadwal_kuliah_dosen.jadwal_kuliah_id', 'ref_person.nama_lengkap'])
            ->get()
            ->groupBy('jadwal_kuliah_id')
            ->map(fn($r) => $r->pluck('nama_lengkap')->implode(', '));

        $items = $rows->map(fn($row) => [
            'hari' => ucfirst(strtolower($row->hari)),
            'jamMulai' => substr($row->jam_mulai, 0, 5),
            'jamSelesai' => substr($row->jam_selesai, 0, 5),
            'kodeMk' => $row->kode_mk_snapshot,
            'namaMk' => $row->nama_mk_snapshot,
            'sks' => $row->sks_snapshot,
            'kelas' => $row->nama_kelas ?? '-',
            'ruang' => $row->nama_ruang ?? '-',
            'dosen' => $dosenPengampu->get($row->jadwal_kuliah_id, '-'),
        ])->values()->all();

        return new JadwalKuliahPdfData(
            mahasiswaId: $mahasiswaId,
            nim: $mahasiswa->nim,
            namaMahasiswa: $mahasiswa->nama_mahasiswa,
            namaProdi: $mahasiswa->nama_prodi,
            namaTahunAkademik: $tahunAkademik->nama_tahun,
            semester: (int) $tahunAkademik->semester,
            items: $items,
            dicetakPada: now()->translatedFormat('d F Y H:i'),
        );
    }
}
