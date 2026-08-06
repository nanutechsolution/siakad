<?php

namespace App\Filament\Resources\KonfigurasiPembimbingAkademiks\Tables;

use App\Enums\PembimbingAkademikMode;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class KonfigurasiPembimbingAkademiksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('prodi_id')
            ->columns([

                TextColumn::make('prodi.nama_prodi')
                    ->label('Program Studi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('angkatan.id_tahun')
                    ->label('Angkatan')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('mode')
                    ->label('Mode')
                    ->badge(),
                IconColumn::make('aktif')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(40)
                    ->tooltip(fn($record) => $record->keterangan),

                TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([

                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->relationship('prodi', 'nama_prodi')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('mode')
                    ->options(PembimbingAkademikMode::options()),
                TernaryFilter::make('aktif')
                    ->label('Status Aktif'),

            ])
            ->recordActions([
                EditAction::make()->slideOver(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])->emptyStateHeading('Belum ada konfigurasi pembimbing')
            ->emptyStateDescription('Buat konfigurasi supaya kombinasi Program Studi & Angkatan ini bisa dipakai untuk Penugasan/Generate Massal Dosen Wali.')
            ->emptyStateIcon('heroicon-o-adjustments-horizontal')
            ->emptyStateActions([
                CreateAction::make()->slideOver(),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
