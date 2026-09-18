<?php

namespace App\Filament\Mahasiswa\Resources\RiwayatKrs\Tables;

use App\Enums\KrsStatusEnum;
use App\Enums\Pdf\PdfDocumentType;
use App\Filament\Actions\Pdf\PdfDownloadAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RiwayatKrsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                /*
                |--------------------------------------------------------------------------
                | Desktop / Mobile Content
                |--------------------------------------------------------------------------
                */

                Stack::make([
                    TextColumn::make('tahunAkademik.nama_tahun')
                        ->label('Tahun Akademik')
                        ->searchable()
                        ->sortable()
                        ->weight('semibold')
                        ->size('lg'),

                    Split::make([
                        TextColumn::make('status_krs')
                            ->label('Status')
                            ->badge()
                            ->color(
                                fn(KrsStatusEnum $state): string =>
                                $state->getColor()
                            )
                            ->formatStateUsing(
                                fn(KrsStatusEnum $state): string =>
                                $state->getLabel()
                            ),

                        TextColumn::make('total_sks_diambil')
                            ->label('Total SKS')
                            ->formatStateUsing(
                                fn($state): string => "{$state} SKS"
                            )
                            ->weight('semibold')
                            ->color('info')
                            ->alignEnd(),
                    ]),

                    TextColumn::make('tgl_krs')
                        ->label('Diajukan')
                        ->dateTime('d M Y, H:i')
                        ->size('sm')
                        ->color('gray'),
                ])
                    ->space(2)
                    ->columnSpanFull(),
            ])

            /*
            |--------------------------------------------------------------------------
            | Responsive Card
            |--------------------------------------------------------------------------
            |
            | Mobile  : 1 card
            | Tablet  : 2 card
            | Desktop : kembali terasa seperti list/table
            |
            */

            ->contentGrid([
                'default' => 1,
                'sm' => 1,
                'md' => 2,
                'lg' => 3,
                'xl' => 3,
            ])

            ->defaultSort('tgl_krs', 'desc')

            ->recordActions([
                ViewAction::make()
                    ->label('Lihat Detail')
                    ->icon('heroicon-m-eye')
                    ->button()
                    ->color('gray')
                    ->authorize(true),

                PdfDownloadAction::make(
                    name: 'cetak-krs',
                    label: 'Cetak KRS',
                    type: PdfDocumentType::KRS,
                    contextResolver: fn($record) => [
                        'krs_id' => $record->id,
                    ],
                )
                    ->icon('heroicon-m-arrow-down-tray')
                    ->button()
                    ->color('primary'),
            ])

            ->striped(false);
    }
}
