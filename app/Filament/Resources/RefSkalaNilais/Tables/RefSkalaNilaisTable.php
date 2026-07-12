<?php

namespace App\Filament\Resources\RefSkalaNilais\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class RefSkalaNilaisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('huruf')
                    ->label('Nilai Huruf')
                    ->weight('bold')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                TextColumn::make('bobot_indeks')
                    ->label('Bobot Indeks')
                    ->numeric(2)
                    ->sortable(),

                TextColumn::make('rentang')
                    ->label('Rentang Angka')
                    ->badge()
                    ->state(fn($record) => "{$record->nilai_min} - {$record->nilai_max}"),

                IconColumn::make('is_lulus')
                    ->label('Lulus?')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
            ])
            ->defaultSort('bobot_indeks', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
