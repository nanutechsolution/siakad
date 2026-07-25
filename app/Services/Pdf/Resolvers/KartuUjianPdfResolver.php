<?php

namespace App\Services\Pdf\Resolvers;

use App\Contracts\Pdf\PdfDataResolverInterface;
use App\DataTransferObjects\Pdf\KartuUjianPdfData;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class KartuUjianPdfResolver implements PdfDataResolverInterface
{
    public function resolve(array $context): KartuUjianPdfData
    {
        $mahasiswaId = $context['mahasiswa_id'] ?? throw new RuntimeException('Context [mahasiswa_id] wajib diisi.');
        $tahunAkademikId = $context['tahun_akademik_id'] ?? throw new RuntimeException('Context [tahun_akademik_id] wajib diisi.');
        $jenisUjian = $context['jenis_ujian'] ?? throw new RuntimeException('Context [jenis_ujian] wajib diisi (UTS/UAS/SUSULAN/LAINNYA).');

        $mahasiswa = DB::table('mahasiswas')
            ->join('ref_person', 'ref_person.id', '=', 'mahasiswas.person_id')
            ->join('ref_prodi', 'ref_prodi.id', '=', 'mahasiswas.prodi_id')
            ->where('mahasiswas.id', $mahasiswaId)
            ->select(['mahasiswas.nim', 'ref_person.nama_lengkap as nama_mahasiswa', 'ref_prodi.nama_prodi'])
            ->first();

        if (! $mahasiswa) {
            throw new RuntimeException("Mahasiswa dengan id [{$mahasiswaId}] tidak ditemukan.");
        }

        $krsId = DB::table('krs')
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('tahun_akademik_id', $tahunAkademikId)
            ->value('id');

        $items = collect();

        if ($krsId) {
            $items = DB::table('jadwal_ujian_pesertas')
                ->join('krs_detail', 'krs_detail.id', '=', 'jadwal_ujian_pesertas.krs_detail_id')
                ->join('jadwal_ujians', 'jadwal_ujians.id', '=', 'jadwal_ujian_pesertas.jadwal_ujian_id')
                ->leftJoin('ref_ruang', 'ref_ruang.id', '=', 'jadwal_ujians.ruang_id')
                ->where('krs_detail.krs_id', $krsId)
                ->where('jadwal_ujians.jenis_ujian', $jenisUjian)
                ->orderBy('jadwal_ujians.tanggal_ujian')
                ->orderBy('jadwal_ujians.jam_mulai')
                ->select([
                    'krs_detail.kode_mk_snapshot',
                    'krs_detail.nama_mk_snapshot',
                    'jadwal_ujians.tanggal_ujian',
                    'jadwal_ujians.jam_mulai',
                    'jadwal_ujians.jam_selesai',
                    'jadwal_ujians.metode_ujian',
                    'ref_ruang.nama_ruang',
                    'jadwal_ujian_pesertas.nomor_kursi',
                ])
                ->get();
        }

        if ($items->isEmpty()) {
            throw new RuntimeException("Tidak ditemukan jadwal ujian [{$jenisUjian}] untuk mahasiswa ini pada tahun akademik yang dipilih.");
        }

        $mapped = $items->map(fn($row) => [
            'kodeMk' => $row->kode_mk_snapshot,
            'namaMk' => $row->nama_mk_snapshot,
            'tanggal' => $row->tanggal_ujian,
            'jamMulai' => substr($row->jam_mulai, 0, 5),
            'jamSelesai' => substr($row->jam_selesai, 0, 5),
            'metode' => $row->metode_ujian,
            'ruang' => $row->nama_ruang ?? '-',
            'nomorKursi' => $row->nomor_kursi ?? '-',
        ])->values()->all();

        return new KartuUjianPdfData(
            mahasiswaId: $mahasiswaId,
            nim: $mahasiswa->nim,
            namaMahasiswa: $mahasiswa->nama_mahasiswa,
            namaProdi: $mahasiswa->nama_prodi,
            jenisUjian: $jenisUjian,
            items: $mapped,
            dicetakPada: now()->translatedFormat('d F Y H:i'),
        );
    }
}
