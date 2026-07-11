<?php

namespace App\Filament\Resources\Gelars\Tables;

use App\Enums\HR\JenjangGelar;
use App\Enums\HR\PosisiGelar;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GelarsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('nama')
                    ->label('Nama Gelar')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('posisi')
                    ->label('Posisi')
                    ->badge()
                    ->color(fn(PosisiGelar $state): string => match ($state) {
                        PosisiGelar::DEPAN => 'info',
                        PosisiGelar::BELAKANG => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('jenjang')
                    ->label('Jenjang')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('posisi')->options(PosisiGelar::class),
                SelectFilter::make('jenjang')->options(JenjangGelar::class),
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
