<?php

namespace App\Filament\Resources\LpmAmiChecklists\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LpmAmiChecklistsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('standar.nama_standar')->label('Standar')->searchable(),
                TextColumn::make('kriteria')->label('Kriteria Audit')->wrap(),
                TextColumn::make('items_count')->label('Jumlah Pertanyaan')->counts('items'),
                TextColumn::make('urutan')->label('Urutan')->sortable(),
            ])
            ->filters([
                SelectFilter::make('standar_id')
                    ->label('Standar')
                    ->relationship('standar', 'nama_standar')
                    ->searchable(),
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
