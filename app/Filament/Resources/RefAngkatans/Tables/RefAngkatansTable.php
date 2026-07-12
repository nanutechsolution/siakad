<?php

namespace App\Filament\Resources\RefAngkatans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class RefAngkatansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_tahun')->label('Tahun Angkatan')->searchable(),
                TextColumn::make('batas_tahun_studi')->label('Batas Studi (Thn)'),
                ToggleColumn::make('is_active_pmb')->label('Buka PMB'),
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
