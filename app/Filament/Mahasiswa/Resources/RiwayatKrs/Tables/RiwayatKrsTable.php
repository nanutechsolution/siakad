<?php

namespace App\Filament\Mahasiswa\Resources\RiwayatKrs\Tables;

use App\Enums\KrsStatusEnum;
use App\Enums\Pdf\PdfDocumentType;
use App\Filament\Actions\Pdf\PdfDownloadAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class RiwayatKrsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // Tahun Akademik
                TextColumn::make('tahunAkademik.nama_tahun')
                    ->label('Tahun Akademik')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description('KRS')
                    ->wrap(),

                // Status
                TextColumn::make('status_krs')
                    ->label('Status')
                    ->badge()
                    ->color(
                        fn(KrsStatusEnum $state): string => $state->getColor()
                    )
                    ->formatStateUsing(
                        fn(KrsStatusEnum $state): string => $state->getLabel()
                    )
                    ->sortable(),

                // Total SKS
                TextColumn::make('total_sks_diambil')
                    ->label('Total SKS')
                    ->formatStateUsing(
                        fn($state): string => "{$state} SKS"
                    )
                    ->weight('semibold')
                    ->color('info')
                    ->sortable(),

                // Tanggal
                TextColumn::make('tgl_krs')
                    ->label('Diajukan')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->color('gray')
                    ->size('sm'),

            ])
            ->defaultSort('tgl_krs', 'desc')

            ->recordActions([

                ViewAction::make()
                    ->label('Lihat')
                    ->icon('heroicon-m-eye')
                    ->authorize(true),

                PdfDownloadAction::make(
                    name: 'cetak-krs',
                    label: 'Cetak KRS',
                    type: PdfDocumentType::KRS,
                    contextResolver: fn($record) => [
                        'krs_id' => $record->id,
                    ],
                )
                    ->icon('heroicon-m-arrow-down-tray'),

            ])

            ->striped(false)
            ->persistSortInSession()
            ->persistSearchInSession();
    }
}
