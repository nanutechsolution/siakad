<?php

namespace App\Filament\Resources\GeneratorBatches\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GeneratorBatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('Batch #')->sortable(),
                TextColumn::make('tahunAkademik.nama_tahun')->label('Tahun Akademik'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'COMPLETED' => 'success',
                        'FAILED' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('total_mahasiswa')->label('Mhs Diperiksa')->numeric(),
                TextColumn::make('total_berhasil')->label('Berhasil')->numeric()->color('success'),
                TextColumn::make('total_skip')->label('Sudah Ada')->numeric()->color('gray'),
                TextColumn::make('total_gagal')->label('Gagal')->numeric()
                    ->color(fn($state) => $state > 0 ? 'danger' : 'gray'),
                TextColumn::make('createdBy.name')->label('Dijalankan Oleh'),
                TextColumn::make('started_at')->label('Mulai')->dateTime('d M Y H:i'),
                TextColumn::make('completed_at')->label('Selesai')->dateTime('d M Y H:i'),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'PROCESSING' => 'Processing',
                    'COMPLETED' => 'Completed',
                    'FAILED' => 'Failed',
                ]),
            ])
            ->defaultSort('id', 'desc');
    }
}
