<?php

namespace App\Filament\Resources\Khs\Pages;

use App\Filament\Resources\Khs\KhsResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewKhs extends ViewRecord
{
    protected static string $resource = KhsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cetak_pdf')
                ->label('Cetak KHS (PDF)')
                ->icon('heroicon-o-printer')
                ->color('danger') // Warna merah agar identik dengan dokumen PDF
                ->url(fn($record) => route('khs.cetak', $record->id)) // Mengarahkan ke route cetak PDF
                ->openUrlInNewTab(), // UX: Buka di tab baru agar halaman KHS tidak tertutup
        ];
    }
}
