<?php

namespace App\Filament\Resources\JadwalGeneratorBatches\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JadwalGeneratorBatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tahunAkademik.nama_tahun')->label('Tahun Akademik'),
                TextColumn::make('prodi.nama_prodi')->label('Program Studi'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'RUNNING' => 'warning',
                        'PREVIEW' => 'info',
                        'COMMITTED' => 'success',
                        'FAILED' => 'danger',
                    }),
                TextColumn::make('total_generated')->label('Sukses')->color('success'),
                TextColumn::make('total_failed')->label('Gagal')->color('danger'),
                TextColumn::make('created_at')->dateTime()->label('Dibuat Pada'),
            ])

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
