<?php

namespace App\Services\Pdf\Resolvers;

use App\Contracts\Pdf\PdfDataResolverInterface;
use App\DataTransferObjects\Pdf\TranskripFinalPdfData;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TranskripFinalPdfResolver implements PdfDataResolverInterface
{
    public function resolve(array $context): TranskripFinalPdfData
    {
        $mahasiswaId = $context['mahasiswa_id'] ?? throw new RuntimeException('Context [mahasiswa_id] wajib diisi.');

        $mahasiswa = DB::table('mahasiswas')
            ->join('ref_person', 'ref_person.id', '=', 'mahasiswas.person_id')
            ->join('ref_prodi', 'ref_prodi.id', '=', 'mahasiswas.prodi_id')
            ->join('ref_fakultas', 'ref_fakultas.id', '=', 'ref_prodi.fakultas_id')
            ->leftJoin('master_kurikulums', 'master_kurikulums.id', '=', 'mahasiswas.kurikulum_id')
            ->where('mahasiswas.id', $mahasiswaId)
            ->select([
                'mahasiswas.nim',
                'mahasiswas.angkatan_id',
                'ref_person.nama_lengkap as nama_mahasiswa',
                'ref_person.tempat_lahir',
                'ref_person.tanggal_lahir',
                'ref_prodi.nama_prodi',
                'ref_prodi.jenjang',
                'ref_fakultas.nama_fakultas',
                'master_kurikulums.nama_kurikulum',
                'master_kurikulums.jumlah_sks_lulus',
            ])
            ->first();

        if (! $mahasiswa) {
            throw new RuntimeException("Mahasiswa dengan id [{$mahasiswaId}] tidak ditemukan.");
        }

        $kodeLulus = config('pdf.kode_status_kuliah_lulus', 'L');

        $statusTerakhir = DB::table('riwayat_status_mahasiswas')
            ->where('mahasiswa_id', $mahasiswaId)
            ->orderByDesc('tahun_akademik_id')
            ->value('status_kuliah');

        if ($statusTerakhir !== $kodeLulus) {
            throw new RuntimeException(
                'Transkrip Final hanya dapat diterbitkan untuk mahasiswa berstatus LULUS. Status akademik terakhir tidak menunjukkan kelulusan.'
            );
        }

        $rows = DB::table('akademik_transkrip')
            ->join('master_mata_kuliahs', 'master_mata_kuliahs.id', '=', 'akademik_transkrip.mata_kuliah_id')
            ->where('akademik_transkrip.mahasiswa_id', $mahasiswaId)
            ->orderBy('master_mata_kuliahs.kode_mk')
            ->select([
                'master_mata_kuliahs.kode_mk',
                'master_mata_kuliahs.nama_mk',
                'akademik_transkrip.sks_diakui',
                'akademik_transkrip.nilai_huruf_final',
                'akademik_transkrip.nilai_indeks_final',
                'akademik_transkrip.is_konversi',
            ])
            ->get();

        if ($rows->isEmpty()) {
            throw new RuntimeException('Belum ada data akademik_transkrip untuk mahasiswa ini — Transkrip Final belum dapat diterbitkan.');
        }

        $totalSks = (int) $rows->sum('sks_diakui');
        $totalBobot = $rows->sum(fn($row) => $row->nilai_indeks_final * $row->sks_diakui);
        $ipk = $totalSks > 0 ? round($totalBobot / $totalSks, 2) : 0.0;

        $items = $rows->map(fn($row) => [
            'kodeMk' => $row->kode_mk,
            'namaMk' => $row->nama_mk,
            'sks' => $row->sks_diakui,
            'nilaiHuruf' => $row->nilai_huruf_final,
            'nilaiIndeks' => number_format((float) $row->nilai_indeks_final, 2),
            'konversi' => (bool) $row->is_konversi,
        ])->values()->all();

        return new TranskripFinalPdfData(
            mahasiswaId: $mahasiswaId,
            nim: $mahasiswa->nim,
            namaMahasiswa: $mahasiswa->nama_mahasiswa,
            tempatLahir: $mahasiswa->tempat_lahir,
            tanggalLahir: $mahasiswa->tanggal_lahir,
            angkatan: (string) $mahasiswa->angkatan_id,
            namaProdi: $mahasiswa->nama_prodi,
            jenjang: $mahasiswa->jenjang,
            namaFakultas: $mahasiswa->nama_fakultas,
            namaKurikulum: $mahasiswa->nama_kurikulum ?? '-',
            syaratSks: (int) ($mahasiswa->jumlah_sks_lulus ?? 144),
            totalSks: $totalSks,
            ipk: number_format($ipk, 2),
            items: $items,
            dicetakPada: now()->translatedFormat('d F Y H:i'),
        );
    }
}
