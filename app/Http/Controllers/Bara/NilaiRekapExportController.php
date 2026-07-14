<?php

namespace App\Http\Controllers\Bara;

use App\Enums\StatusNilaiKelas as EnumsStatusNilaiKelas;
use App\Models\JadwalKuliah;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NilaiRekapExportController
{
    /**
     * Export rekap sesuai filter yang sedang aktif di tabel monitoring.
     * Menerima query string yang sama dengan Filament table filters:
     *   ?tableFilters[tahun_akademik_id][value]=1&tableFilters[prodi][value]=2 ...
     */
    public function __invoke(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->can('export_nilai'), 403);

        $filters = $request->query('tableFilters', []);

        $query = JadwalKuliah::query()
            ->withNilaiStats()
            ->with([
                'tahunAkademik', 'mataKuliah', 'kelas.prodi.fakultas', 'dosenPengampu.person',
            ])
            ->when(
                data_get($filters, 'tahun_akademik_id.value'),
                fn ($q, $v) => $q->where('tahun_akademik_id', $v)
            )
            ->when(
                data_get($filters, 'prodi.value'),
                fn ($q, $v) => $q->whereHas('kelas', fn ($qq) => $qq->where('prodi_id', $v))
            )
            ->when(
                data_get($filters, 'dosen.value'),
                fn ($q, $v) => $q->whereHas('dosenPengampu', fn ($qq) => $qq->where('trx_dosen.id', $v))
            )
            ->when(
                data_get($filters, 'status_nilai.value'),
                fn ($q, $v) => $q->statusNilai(EnumsStatusNilaiKelas::from($v))
            );

        $filename = 'rekap-nilai-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Tahun Akademik', 'Semester', 'Fakultas', 'Program Studi', 'Mata Kuliah',
                'Kelas Kuliah', 'Dosen Pengampu', 'Jumlah Mahasiswa', 'Status Nilai',
            ]);

            $query->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $row) {
                    fputcsv($out, [
                        $row->tahunAkademik?->nama_tahun,
                        match ($row->tahunAkademik?->semester) {
                            1 => 'Ganjil', 2 => 'Genap', 3 => 'Pendek', default => '-',
                        },
                        $row->kelas?->prodi?->fakultas?->nama_fakultas,
                        $row->kelas?->prodi?->nama_prodi,
                        $row->mataKuliah?->nama_mk,
                        $row->kelas?->nama_kelas,
                        $row->dosenPengampu->pluck('person.nama_lengkap')->implode(', '),
                        $row->jumlah_mahasiswa,
                        $row->status_nilai->label(),
                    ]);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}