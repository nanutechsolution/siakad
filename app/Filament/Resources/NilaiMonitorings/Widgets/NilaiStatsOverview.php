<?php

namespace App\Filament\Resources\NilaiMonitorings\Widgets;

use App\Enums\StatusNilaiKelas;
use App\Models\JadwalKuliah;
use App\Models\KrsDetail;
use App\Models\RefTahunAkademik;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class NilaiStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $tahunAktif = RefTahunAkademik::where('is_active', true)->first();

        $baseJadwal = JadwalKuliah::query()
            ->when($tahunAktif, fn($q) => $q->where('tahun_akademik_id', $tahunAktif->id))
            ->withNilaiStats();

        $totalMkAktif = (clone $baseJadwal)->count();

        $belumLengkap = (clone $baseJadwal)
            ->statusNilai(StatusNilaiKelas::SEBAGIAN_INPUT)
            ->orWhere(fn($q) => $q->statusNilai(StatusNilaiKelas::BELUM_INPUT))
            ->count();

        $belumPublish = (clone $baseJadwal)->statusNilai(StatusNilaiKelas::SUDAH_INPUT)->count();

        $sudahFinal = (clone $baseJadwal)->statusNilai(StatusNilaiKelas::TERKUNCI)->count();

        $mahasiswaBelumNilai = KrsDetail::query()
            ->whereNull('nilai_huruf')
            ->when($tahunAktif, fn($q) => $q->whereHas(
                'jadwalKuliah',
                fn($qq) => $qq->where('tahun_akademik_id', $tahunAktif->id)
            ))
            ->count();

        return [
            Stat::make('Total Mata Kuliah Aktif', $totalMkAktif)
                ->description($tahunAktif?->nama_tahun ?? 'Tahun akademik aktif belum ditentukan')
                ->color('primary')
                ->icon('heroicon-o-academic-cap'),

            Stat::make('Nilai Belum Lengkap', $belumLengkap)
                ->description('Kelas dengan input belum 100%')
                ->color('danger')
                ->icon('heroicon-o-exclamation-triangle'),

            Stat::make('Nilai Belum Publish', $belumPublish)
                ->description('Sudah lengkap, menunggu publish dosen')
                ->color('info')
                ->icon('heroicon-o-pencil-square'),

            Stat::make('Nilai Sudah Final', $sudahFinal)
                ->description('Sudah dikunci BARA')
                ->color('success')
                ->icon('heroicon-o-lock-closed'),

            Stat::make('Mahasiswa Belum Ada Nilai', $mahasiswaBelumNilai)
                ->description('Baris krs_detail dengan nilai_huruf kosong')
                ->color('warning')
                ->icon('heroicon-o-user-minus'),
        ];
    }
}
