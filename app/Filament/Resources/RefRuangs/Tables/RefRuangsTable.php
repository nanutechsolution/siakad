<?php

namespace App\Filament\Resources\RefRuangs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class RefRuangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_ruang')->searchable()->weight('bold'),
                TextColumn::make('nama_ruang')->searchable(),

                TextColumn::make('kapasitas')
                    ->sortable()
                    ->color(fn($state) => $state < 20 ? 'danger' : 'success'),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),

                // Visualisasi koordinat simpel
                TextColumn::make('lokasi')
                    ->label('Koordinat')
                    ->state(fn($record) => $record->latitude ? "{$record->latitude}, {$record->longitude}" : 'N/A')
                    ->size('xs')
                    ->color('gray'),
            ])
            ->filters([
                Filter::make('active')->query(fn($query) => $query->where('is_active', true)),
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
