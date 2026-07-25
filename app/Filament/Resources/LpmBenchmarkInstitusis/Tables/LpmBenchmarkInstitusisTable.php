<?php

namespace App\Filament\Resources\LpmBenchmarkInstitusis\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LpmBenchmarkInstitusisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_institusi')->label('Nama Institusi')->searchable(),
                TextColumn::make('jenis')->label('Jenis')->badge(),
                TextColumn::make('benchmarks_count')->label('Jumlah Data Pembanding')->counts('benchmarks'),
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
