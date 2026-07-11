<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Keuangan;

use App\Models\KeuanganMahasiswaBeasiswa;
use App\Models\RefTahunAkademik;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RingkasanBeasiswaStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // TTL 15 Menit (900 detik) - Agregasi data tagihan sangat berat, wajib di-cache
        $stats = Cache::remember('keuangan_beasiswa_stats', 900, function () {
            $tahunAktif = RefTahunAkademik::where('is_active', true)->first();
            
            $totalPenerimaAktif = KeuanganMahasiswaBeasiswa::where('is_active', true)->count();
            
            // Agregasi nilai diskon dari tagihan yang di-generate pada tahun akademik aktif
            $totalDiskonBerjalan = 0;
            if ($tahunAktif) {
                $totalDiskonBerjalan = DB::table('tagihan_mahasiswas_details')
                    ->join('tagihan_mahasiswas', 'tagihan_mahasiswas.id', '=', 'tagihan_mahasiswas_details.tagihan_id')
                    ->where('tagihan_mahasiswas.tahun_akademik_id', $tahunAktif->id)
                    ->sum('tagihan_mahasiswas_details.nominal_diskon');
            }

            // Breakdown sumber beasiswa
            $internalCount = KeuanganMahasiswaBeasiswa::where('is_active', true)
                ->whereHas('beasiswa', fn ($q) => $q->where('kategori', 'INTERNAL'))
                ->count();
                
            return [
                'penerima_aktif' => $totalPenerimaAktif,
                'total_diskon' => (float) $totalDiskonBerjalan,
                'internal_count' => $internalCount,
                'tahun_aktif' => $tahunAktif ? $tahunAktif->nama_tahun : 'Belum Ada',
            ];
        });

        return [
            Stat::make('Penerima Beasiswa Aktif', number_format($stats['penerima_aktif']) . ' Mahasiswa')
                ->description('Total dari semua program beasiswa')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Total Diskon (TA ' . $stats['tahun_aktif'] . ')', 'Rp ' . number_format($stats['total_diskon'], 0, ',', '.'))
                ->description('Beban subsidi institusi berjalan')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('danger'),

            Stat::make('Penerima Internal Yayasan', number_format($stats['internal_count']) . ' Mahasiswa')
                ->description('Didanai oleh anggaran internal institusi')
                ->descriptionIcon('heroicon-m-building-library')
                ->color('info'),
        ];
    }
}