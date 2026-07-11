<?php

namespace App\Filament\Resources\MasterBeasiswas\Tables;

use App\Enums\Keuangan\KategoriBeasiswa;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class MasterBeasiswasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_beasiswa')
                    ->label('Nama Beasiswa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('details_count')
                    ->label('Jml Komponen')
                    ->counts('details')
                    ->badge()
                    ->color('info'),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kategori')
                    ->options(KategoriBeasiswa::class),
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
                TrashedFilter::make()
                    ->label('Data Terhapus'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
