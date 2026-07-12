<?php

namespace App\Filament\Dosen\Resources\JadwalMengajars\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JadwalMengajarsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mataKuliah.kode_mk')
                    ->label('Kode MK')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mataKuliah.nama_mk')
                    ->label('Mata Kuliah')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->badge()
                    ->sortable(),
                TextColumn::make('hari')
                    ->label('Hari')
                    ->sortable(),
                TextColumn::make('jam_mulai')
                    ->label('Jam')
                    ->time('H:i')
                    ->formatStateUsing(fn($state, $record) => $state . ' - ' . \Carbon\Carbon::parse($record->jam_selesai)->format('H:i')),
                TextColumn::make('ruang.nama_ruang')
                    ->label('Ruangan')
                    ->searchable(),
                TextColumn::make('isi_kelas')
                    ->label('Peserta')
                    ->numeric()
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->authorize(true)
                    ->label('Detail & Sesi'),
            ]);
    }
}
