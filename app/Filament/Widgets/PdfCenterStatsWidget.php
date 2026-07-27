<?php

namespace App\Filament\Widgets;

use App\Models\PdfDocument;
use App\Models\PdfVerification;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PdfCenterStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Dokumen Diterbitkan Hari Ini', PdfDocument::whereDate('generated_at', today())->count())
                ->icon('heroicon-o-document-arrow-down')
                ->color('success'),

            Stat::make('Total Dokumen Terarsip', PdfDocument::where('classification', 'archived')->count())
                ->icon('heroicon-o-archive-box')
                ->color('info'),

            Stat::make('Verifikasi QR (30 Hari)', PdfVerification::where('created_at', '>=', now()->subDays(30))->count())
                ->icon('heroicon-o-qr-code')
                ->color('warning'),

            Stat::make('Percobaan Verifikasi Gagal (30 Hari)', PdfVerification::where('created_at', '>=', now()->subDays(30))->where('ditemukan', false)->count())
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
