<?php

namespace App\Filament\Resources\LpmAkreditasiLembagas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LpmAkreditasiLembagasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode')->label('Kode')->searchable(),
                TextColumn::make('nama')->label('Nama')->searchable(),
                TextColumn::make('jenis')->label('Jenis')->badge(),
                TextColumn::make('akreditasis_count')->label('Jumlah Proses')->counts('akreditasis'),
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
