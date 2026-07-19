<?php

declare(strict_types=1);

namespace App\Services\LaporanPerkuliahan;

use App\Models\JadwalKuliah;
use Illuminate\Database\Eloquent\Builder;

/**
 * NOTE: Service ini mengasumsikan model-model berikut sudah ada dan memetakan
 * ke tabel schema yang diberikan (silakan sesuaikan namespace jika berbeda):
 * JadwalKuliah (jadwal_kuliah), JadwalKuliahDosen (jadwal_kuliah_dosen),
 * Kelas (kelas), MasterMataKuliah (master_mata_kuliahs), Ruang (ref_ruang),
 * Prodi (ref_prodi), Fakultas (ref_fakultas), TahunAkademik (ref_tahun_akademik),
 * TrxDosen (trx_dosen), Person (ref_person), Mahasiswa (mahasiswas),
 * Krs (krs), KrsDetail (krs_detail), PerkuliahanSesi (perkuliahan_sesi),
 * PerkuliahanAbsensi (perkuliahan_absensi).
 */
class JadwalKuliahReportService
{
    /**
     * @param  array{tahun_akademik_id?: int, semester?: int, fakultas_id?: int, prodi_id?: int, dosen_id?: string, mata_kuliah_id?: int, ruang_id?: int}  $filters
     */
    public function query(array $filters = []): Builder
    {
        return JadwalKuliah::query()
            ->with([
                'tahunAkademik',
                'mataKuliah',
                'ruang',
                'kelas.prodi.fakultas',
                'dosenPengajars.dosen.person', // Perbaikan rantai Eager Loading
            ])
            ->when(
                $filters['tahun_akademik_id'] ?? null,
                fn(Builder $query, $value) => $query->where('tahun_akademik_id', $value)
            )
            ->when(
                $filters['semester'] ?? null,
                fn(Builder $query, $value) => $query->whereHas(
                    'tahunAkademik',
                    fn(Builder $q) => $q->where('semester', $value)
                )
            )
            ->when(
                $filters['fakultas_id'] ?? null,
                fn(Builder $query, $value) => $query->whereHas(
                    'kelas.prodi',
                    fn(Builder $q) => $q->where('fakultas_id', $value)
                )
            )
            ->when(
                $filters['prodi_id'] ?? null,
                fn(Builder $query, $value) => $query->whereHas(
                    'kelas',
                    fn(Builder $q) => $q->where('prodi_id', $value)
                )
            )
            ->when(
                $filters['dosen_id'] ?? null,
                fn(Builder $query, $value) => $query->whereHas(
                    'dosenPengajars', // Perbaikan nama relasi
                    fn(Builder $q) => $q->where('dosen_id', $value)
                )
            )
            ->when(
                $filters['mata_kuliah_id'] ?? null,
                fn(Builder $query, $value) => $query->where('mata_kuliah_id', $value)
            )
            ->when(
                $filters['ruang_id'] ?? null,
                fn(Builder $query, $value) => $query->where('ruang_id', $value)
            )
            ->orderByRaw("FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")
            ->orderBy('jam_mulai');
    }

    public function exportRows(array $filters = []): \Illuminate\Support\Collection
    {
        return $this->query($filters)->get()->map(fn(JadwalKuliah $jadwal) => [
            'hari' => $jadwal->hari,
            'jam_mulai' => optional($jadwal->jam_mulai)->format('H:i'),
            'jam_selesai' => optional($jadwal->jam_selesai)->format('H:i'),
            'kode_mk' => $jadwal->mataKuliah?->kode_mk,
            'nama_mk' => $jadwal->mataKuliah?->nama_mk,
            'sks' => $jadwal->mataKuliah?->sks_default,
            'dosen' => $jadwal->dosenPengajars->map(fn($d) => $d->dosen?->person?->nama_lengkap)->filter()->implode(', '),
            'prodi' => $jadwal->kelas?->prodi?->nama_prodi,
            'ruang' => $jadwal->ruang?->nama_ruang,
            'kelas' => $jadwal->kelas?->nama_kelas,
        ]);
    }
}