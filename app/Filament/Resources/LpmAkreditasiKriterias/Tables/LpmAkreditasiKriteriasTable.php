<?php

namespace App\Filament\Resources\LpmAkreditasiKriterias\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LpmAkreditasiKriteriasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_kriteria')->label('Kode'),
                TextColumn::make('nama_kriteria')->label('Nama Kriteria')->wrap(),
                TextColumn::make('elemens_count')->label('Jumlah Elemen')->counts('elemens'),
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
