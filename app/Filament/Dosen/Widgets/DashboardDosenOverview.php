<?php

declare(strict_types=1);

namespace App\Filament\Dosen\Widgets;

use App\Models\JadwalKuliah;
use App\Models\JadwalUjian;
use App\Models\Mahasiswa;
use App\Models\KrsDetail;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class DashboardDosenOverview extends BaseWidget
{
    protected  ?string $pollingInterval = '30s'; // Mencegah beban server berlebih
    protected function getStats(): array
    {
        $user = Auth::user();

        // Pengambilan ID berdasarkan pola data user -> person -> trxDosen Anda
        $dosenId = $user?->person?->trxDosen?->id;
        $personId = $user?->person?->id;

        if (!$dosenId || !$personId) {
            return [];
        }

        // 1. STATS: Total Kelas Mengajar Aktif
        $totalKelasMengajar = JadwalKuliah::query()
            ->whereHas('tahunAkademik', fn(Builder $q) => $q->where('is_active', true))
            ->whereHas('dosenPengajar', function (Builder $q) use ($dosenId) {
                $q->where('dosen_id', $dosenId);
            })
            ->count();

        // 2. STATS: Mahasiswa Perwalian (Dosen Wali Utama)
        // Menghitung mahasiswa yang berada di kelas di mana dosen ini diset sebagai is_primary di kelas_dosen_wali
        $totalMahasiswaWali = Mahasiswa::query()
            ->whereHas('kelas', function (Builder $q) use ($dosenId) {
                $q->whereHas('kelasDosenWalis', function (Builder $dq) use ($dosenId) {
                    $dq->where('dosen_id', $dosenId)->where('is_primary', true);
                });
            })
            ->count();

        // 3. STATS: Agenda Mengawas Ujian Terdekat
        // Berdasarkan relasi jadwalUjian -> pengawas (tabel jadwal_ujian_pengawas mencari person_id)
        $agendaUjianMendatang = JadwalUjian::query()
            ->where('tanggal_ujian', '>=', now()->toDateString())
            ->whereHas('pengawas', function (Builder $q) use ($personId) {
                $q->where('person_id', $personId);
            })
            ->count();

        // 4. STATS: Tanggungan Input Nilai Mahasiswa
        // Menghitung krs_detail yang belum dipublikasi di kelas yang diajar dosen tersebut selaku penilai
        $tanggunganBelumDinilai = KrsDetail::query()
            ->where('is_published', false)
            ->whereHas('jadwalKuliah', function (Builder $q) use ($dosenId) {
                $q->whereHas('tahunAkademik', fn(Builder $ta) => $ta->where('is_active', true))
                    ->whereHas('dosenPengajar', function (Builder $dp) use ($dosenId) {
                        $dp->where('dosen_id', $dosenId)->where('is_penilai', true);
                    });
            })
            ->count();

        return [
            Stat::make('Kelas Mengajar', $totalKelasMengajar . ' Kelas')
                ->description('Semester aktif saat ini')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),

            Stat::make('Mahasiswa Bimbingan', $totalMahasiswaWali . ' Mhs')
                ->description('Status sebagai Dosen Wali Utama')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Jadwal Mengawas Ujian', $agendaUjianMendatang . ' Sesi')
                ->description('Ujian aktif & mendatang')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color($agendaUjianMendatang > 0 ? 'warning' : 'gray'),

            Stat::make('Tanggungan Input Nilai', $tanggunganBelumDinilai . ' Mhs')
                ->description($tanggunganBelumDinilai > 0 ? 'Ada nilai yang belum di-publish!' : 'Semua nilai sudah di-publish')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($tanggunganBelumDinilai > 0 ? 'danger' : 'success')
                ->chart($tanggunganBelumDinilai > 0 ? [5, 12, 8, 20, $tanggunganBelumDinilai] : [0, 0, 0]),
        ];
    }
}
