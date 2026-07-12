<?php

namespace App\Filament\Resources\RefAturanSks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RefAturanSksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('min_ips')->label('Min IPS')->numeric(2)->sortable(),
                TextColumn::make('max_ips')->label('Max IPS')->numeric(2)->sortable(),
                TextColumn::make('max_sks')
                    ->label('Max SKS')
                    ->badge()
                    ->color('primary')
                    ->weight('bold'),
            ])
            ->filters([
                //
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
