<?php

namespace App\Filament\Resources\LpmStandars\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class LpmStandarsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_standar')->label('Kode')->searchable(),
                TextColumn::make('nama_standar')->label('Nama Standar')->searchable()->wrap(),
                TextColumn::make('kategoriStandar.nama')->label('Kategori'),
                TextColumn::make('versi')->label('Versi')->badge(),
                TextColumn::make('indikators_count')->label('Jumlah Indikator')->counts('indikators'),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->filters([
                SelectFilter::make('kategori_standar_id')
                    ->label('Kategori Standar')
                    ->relationship('kategoriStandar', 'nama'),
                TernaryFilter::make('is_active')->label('Status Aktif'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])->defaultSort('kode_standar');
    }
}
