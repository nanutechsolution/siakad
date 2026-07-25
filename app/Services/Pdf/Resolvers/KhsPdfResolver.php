<?php

namespace App\Services\Pdf\Resolvers;

use App\Contracts\Pdf\PdfDataResolverInterface;
use App\DataTransferObjects\Pdf\KhsPdfData;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class KhsPdfResolver implements PdfDataResolverInterface
{
    public function resolve(array $context): KhsPdfData
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

        $riwayat = DB::table('riwayat_status_mahasiswas')
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('tahun_akademik_id', $tahunAkademikId)
            ->first();

        if (! $riwayat) {
            throw new RuntimeException('Belum ada rekap IPS/IPK untuk mahasiswa pada tahun akademik ini — KHS belum dapat dicetak.');
        }

        $krsId = DB::table('krs')
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('tahun_akademik_id', $tahunAkademikId)
            ->value('id');

        $details = collect();

        if ($krsId) {
            $details = DB::table('krs_detail')
                ->where('krs_id', $krsId)
                ->where('is_published', true)
                ->orderBy('id')
                ->select([
                    'kode_mk_snapshot',
                    'nama_mk_snapshot',
                    'sks_snapshot',
                    'nilai_huruf',
                    'nilai_indeks',
                    'nilai_angka',
                    'status_ambil',
                    'updated_at',
                ])
                ->get();
        }

        if ($details->isEmpty()) {
            throw new RuntimeException('Nilai untuk semester ini belum dipublikasikan — KHS belum dapat dicetak.');
        }

        $items = $details->map(fn($row) => [
            'kodeMk' => $row->kode_mk_snapshot,
            'namaMk' => $row->nama_mk_snapshot,
            'sks' => $row->sks_snapshot,
            'nilaiHuruf' => $row->nilai_huruf,
            'nilaiIndeks' => number_format((float) $row->nilai_indeks, 2),
            'statusAmbil' => $row->status_ambil,
        ])->values()->all();

        $lastDetailUpdate = $details->max('updated_at');
        $sourceUpdatedAt = (string) max($lastDetailUpdate, $riwayat->updated_at);

        return new KhsPdfData(
            mahasiswaId: $mahasiswaId,
            nim: $mahasiswa->nim,
            namaMahasiswa: $mahasiswa->nama_mahasiswa,
            namaProdi: $mahasiswa->nama_prodi,
            namaTahunAkademik: $tahunAkademik->nama_tahun,
            semester: (int) $tahunAkademik->semester,
            ips: number_format((float) $riwayat->ips, 2),
            ipk: number_format((float) $riwayat->ipk, 2),
            sksSemester: (int) $riwayat->sks_semester,
            sksTotal: (int) $riwayat->sks_total,
            statusKuliah: $riwayat->status_kuliah,
            items: $items,
            dicetakPada: now()->translatedFormat('d F Y H:i'),
            sourceUpdatedAt: $sourceUpdatedAt,
        );
    }
}
