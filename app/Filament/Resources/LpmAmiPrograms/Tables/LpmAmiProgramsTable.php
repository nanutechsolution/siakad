<?php

namespace App\Filament\Resources\LpmAmiPrograms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LpmAmiProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('periode.nama_periode')->label('Periode')->searchable(),
                TextColumn::make('unitKerja.nama_unit')->label('Unit Diaudit')->searchable(),
                TextColumn::make('tanggal_pelaksanaan')->label('Tanggal')->date('d/m/Y'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'SELESAI' => 'success',
                        'BERLANGSUNG' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('programAuditors_count')->label('Jumlah Auditor')->counts('programAuditors'),
                TextColumn::make('findings_count')->label('Jumlah Temuan')->counts('findings'),
            ])
            ->filters([
                SelectFilter::make('periode_id')
                    ->label('Periode')
                    ->relationship('periode', 'nama_periode'),
                SelectFilter::make('status')
                    ->options([
                        'DIJADWALKAN' => 'Dijadwalkan',
                        'BERLANGSUNG' => 'Berlangsung',
                        'SELESAI' => 'Selesai',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])->defaultSort('tanggal_pelaksanaan', 'desc');
    }
}
