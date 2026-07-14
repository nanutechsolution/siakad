<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class KeuanganOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $tahunAktif = DB::table('ref_tahun_akademik')
            ->where('is_active', 1)
            ->first();

        $baseTagihan = DB::table('tagihan_mahasiswas')->whereNull('deleted_at');

        if ($tahunAktif) {
            $baseTagihan->where('tahun_akademik_id', $tahunAktif->id);
        }

        $totalTagihan = (clone $baseTagihan)->sum('total_tagihan');
        $totalTerbayar = (clone $baseTagihan)->sum('total_bayar');

        $totalTunggakan = (clone $baseTagihan)
            ->where('status_bayar', '!=', 'LUNAS')
            ->sum(DB::raw('total_tagihan - total_bayar'));

        $mahasiswaMenunggak = (clone $baseTagihan)
            ->where('status_bayar', '!=', 'LUNAS')
            ->distinct()
            ->count('mahasiswa_id');

        $pembayaranMenunggu = DB::table('pembayaran_mahasiswas')
            ->join('ref_status_verifikasi_pembayaran', 'ref_status_verifikasi_pembayaran.id', '=', 'pembayaran_mahasiswas.status_verifikasi_id')
            ->where('ref_status_verifikasi_pembayaran.is_final', 0)
            ->whereNull('pembayaran_mahasiswas.deleted_at')
            ->count();

        return [
            Stat::make('Total Tagihan', 'Rp ' . number_format((float) $totalTagihan, 0, ',', '.'))
                ->description($tahunAktif ? $tahunAktif->nama_tahun : 'Seluruh periode')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('Total Terbayar', 'Rp ' . number_format((float) $totalTerbayar, 0, ',', '.'))
                ->description('Kumulatif pembayaran masuk')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Tunggakan', 'Rp ' . number_format((float) $totalTunggakan, 0, ',', '.'))
                ->description($mahasiswaMenunggak . ' mahasiswa belum lunas')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($totalTunggakan > 0 ? 'danger' : 'success'),

            Stat::make('Menunggu Verifikasi', number_format($pembayaranMenunggu))
                ->description($pembayaranMenunggu > 0 ? 'Perlu diverifikasi' : 'Semua sudah diverifikasi')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pembayaranMenunggu > 0 ? 'warning' : 'success'),
        ];
    }
}