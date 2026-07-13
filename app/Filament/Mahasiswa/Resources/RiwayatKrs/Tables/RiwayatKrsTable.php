<?php

namespace App\Filament\Mahasiswa\Resources\RiwayatKrs\Tables;

use App\Enums\KrsStatusEnum;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RiwayatKrsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tahunAkademik.nama_tahun')
                    ->label('Tahun Akademik')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('tgl_krs')
                    ->label('Tanggal Pengajuan')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('total_sks_diambil')
                    ->label('Total SKS')
                    ->badge()
                    ->color('info'),
                    

                TextColumn::make('status_krs')
                    ->label('Status')
                    ->badge()
                    ->color(fn(KrsStatusEnum $state): string => $state->getColor())
                    ->formatStateUsing(fn(KrsStatusEnum $state): string => $state->getLabel()),
            ])
            ->defaultSort('tgl_krs', 'desc')
            ->recordActions([
                ViewAction::make()->label('Lihat Detail')->authorize(true),
            ]);
    }
}
