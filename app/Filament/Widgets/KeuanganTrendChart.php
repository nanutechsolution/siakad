<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class KeuanganTrendChart extends ChartWidget
{
    protected ?string $heading = 'Tren Pembayaran Terverifikasi (6 Bulan Terakhir)';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $months = collect(range(5, 0))
            ->map(fn ($i) => now()->subMonths($i)->format('Y-m'));

        $rows = DB::table('pembayaran_mahasiswas')
            ->join('ref_status_verifikasi_pembayaran', 'ref_status_verifikasi_pembayaran.id', '=', 'pembayaran_mahasiswas.status_verifikasi_id')
            ->where('ref_status_verifikasi_pembayaran.is_final', 1)
            ->whereNull('pembayaran_mahasiswas.deleted_at')
            ->where('pembayaran_mahasiswas.tanggal_bayar', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(pembayaran_mahasiswas.tanggal_bayar, '%Y-%m') as bulan, SUM(pembayaran_mahasiswas.nominal_bayar) as total")
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        return [
            'datasets' => [
                [
                    'label' => 'Pembayaran Terverifikasi (Rp)',
                    'data' => $months->map(fn ($m) => (float) ($rows[$m] ?? 0))->toArray(),
                    'borderColor' => '#C9A227',
                    'backgroundColor' => 'rgba(201, 162, 39, 0.15)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $months
                ->map(fn ($m) => Carbon::createFromFormat('Y-m', $m)->translatedFormat('M Y'))
                ->toArray(),
        ];
    }
}