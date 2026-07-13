<?php

namespace App\Filament\Resources\BankKampuses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class BankKampusesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular(),

                TextColumn::make('nama_bank')
                    ->label('Bank')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('no_rekening')
                    ->label('No. Rekening')
                    ->copyable() // Staf bisa copy paste no rekening dengan 1 klik
                    ->searchable(),

                TextColumn::make('atas_nama')
                    ->label('Atas Nama')
                    ->searchable(),

                ToggleColumn::make('is_active')
                    ->label('Aktif')
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
