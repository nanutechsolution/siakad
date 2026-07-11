<?php

namespace App\Filament\Resources\Kelas\Tables;

use App\Models\Kelas;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class KelasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_kelas')
                    ->label('Nama Kelas')
                    ->searchable()
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('prodi.nama_prodi')
                    ->label('Program Studi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('program.nama_program')
                    ->label('Program')
                    ->badge()
                    ->color('info'),

                TextColumn::make('angkatan_id')
                    ->label('Angkatan')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('kapasitas')
                    ->label('Kapasitas')
                    ->numeric()
                    ->alignCenter(),

                TextColumn::make('mahasiswas_count')
                    ->label('Isi Kelas')
                    ->counts('mahasiswas')
                    ->badge()
                    ->color(fn(int $state, Kelas $record): string => $state >= $record->kapasitas ? 'danger' : 'success')
                    ->alignCenter(),
                // Indicator::make('kapasitas_progress')
                //     ->label('Kapasitas')
                //     ->state(fn(Kelas $record): float => ($record->mahasiswas_count / $record->kapasitas) * 100)
                //     ->color(fn(int $state): string => match (true) {
                //         $state >= 100 => 'danger',
                //         $state >= 80 => 'warning',
                //         default => 'success',
                //     }),
            ])
            ->filters([
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->options(DB::table('ref_prodi')->pluck('nama_prodi', 'id')),

                SelectFilter::make('angkatan_id')
                    ->label('Angkatan')
                    ->options(DB::table('ref_angkatan')->pluck('id_tahun', 'id_tahun')),
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
