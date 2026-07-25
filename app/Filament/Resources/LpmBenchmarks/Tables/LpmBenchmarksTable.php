<?php

namespace App\Filament\Resources\LpmBenchmarks\Tables;

use App\Models\LpmBenchmark;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LpmBenchmarksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('indikator.nama_indikator')->label('Indikator')->searchable()->wrap(),
                TextColumn::make('institusiPembanding.nama_institusi')->label('Institusi Pembanding')->searchable(),
                TextColumn::make('tahun')->label('Tahun')->sortable(),
                TextColumn::make('nilai_internal')->label('Nilai Internal')->numeric(2),
                TextColumn::make('nilai_eksternal')->label('Nilai Eksternal')->numeric(2),
                TextColumn::make('gap')
                    ->label('Gap')
                    ->state(fn(LpmBenchmark $record) => $record->gap())
                    ->badge()
                    ->color(fn(?string $state) => match (true) {
                        $state === null => 'gray',
                        (float) $state >= 0 => 'success',
                        default => 'danger',
                    }),
            ])
            ->filters([
                SelectFilter::make('institusi_pembanding_id')
                    ->label('Institusi Pembanding')
                    ->relationship('institusiPembanding', 'nama_institusi'),
                SelectFilter::make('tahun')
                    ->options(fn() => LpmBenchmark::query()->distinct()->orderByDesc('tahun')->pluck('tahun', 'tahun')),
            ])->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])->defaultSort('tahun', 'desc');
    }
}
