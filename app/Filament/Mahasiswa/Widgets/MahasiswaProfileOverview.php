<?php

namespace App\Filament\Mahasiswa\Widgets;

use App\Enums\StatusKuliah;
use App\Models\Mahasiswa;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MahasiswaProfileOverview extends StatsOverviewWidget
{
   protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = Auth::user();

        // Jembatan Auth: Cari Mahasiswa berdasarkan person_id user yang login
        $mahasiswa = Mahasiswa::with(['prodi', 'person'])
            ->where('person_id', $user->person_id)
            ->first();

        if (!$mahasiswa) {
            return [
                Stat::make('Status Akses', 'Akses Ditolak')
                    ->description('Akun Anda tidak terhubung dengan data Mahasiswa.')
                    ->color('danger'),
            ];
        }

        // Ambil riwayat semester terakhir untuk IPK dan SKS
        $riwayatTerakhir = DB::table('riwayat_status_mahasiswas')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->orderBy('tahun_akademik_id', 'desc')
            ->first();

        $ipk = $riwayatTerakhir ? number_format((float) $riwayatTerakhir->ipk, 2, '.', '') : '0.00';
        $sksTotal = $riwayatTerakhir ? (string) $riwayatTerakhir->sks_total : '0';
        
        $rawStatus = $riwayatTerakhir ? $riwayatTerakhir->status_kuliah : StatusKuliah::AKTIF->value;
        $statusLabel = StatusKuliah::tryFrom($rawStatus)?->label() ?? 'Tidak Diketahui';
        
        $statusColor = match ($rawStatus) {
            StatusKuliah::AKTIF->value => 'success',
            StatusKuliah::CUTI->value => 'warning',
            default => 'danger',
        };

        return [
            Stat::make('Status Mahasiswa', $statusLabel)
                ->description($mahasiswa->nim . ' - ' . ($mahasiswa->prodi->nama_prodi ?? 'Prodi Tidak Diketahui'))
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color($statusColor),

            Stat::make('IPK (Indeks Prestasi Kumulatif)', $ipk)
                ->description('Dari maksimal 4.00')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),

            Stat::make('Total SKS Ditempuh', $sksTotal . ' SKS')
                ->description('Syarat kelulusan: 144 SKS')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('success'),
        ];
    }
}
