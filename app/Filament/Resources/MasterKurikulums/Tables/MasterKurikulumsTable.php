<?php

namespace App\Filament\Resources\MasterKurikulums\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MasterKurikulumsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('prodi.nama_prodi')
                    ->label('Program Studi')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nama_kurikulum')
                    ->label('Kurikulum')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('tahun_mulai')
                    ->label('Tahun')
                    ->sortable(),

                TextColumn::make('jumlah_sks_lulus')
                    ->label('SKS Lulus')
                    ->numeric()
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('no_sk_kurikulum')
                    ->label('No. SK')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->relationship('prodi', 'nama_prodi') // Sesuaikan kolom text prodi Anda
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
