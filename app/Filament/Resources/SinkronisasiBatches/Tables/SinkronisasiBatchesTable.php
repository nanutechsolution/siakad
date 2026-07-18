<?php

namespace App\Filament\Resources\SinkronisasiBatches\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SinkronisasiBatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('Batch #')->sortable(),
                TextColumn::make('tahunAkademik.nama_tahun')->label('Tahun Akademik'),
                TextColumn::make('mode')
                    ->badge()
                    ->color(fn(string $state) => $state === 'DRY_RUN' ? 'gray' : 'primary'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'COMPLETED' => 'success',
                        'FAILED' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('total_mahasiswa')->label('Mhs Diperiksa')->numeric(),
                TextColumn::make('total_ditambah')->label('Ditambah')->numeric()->color('success'),
                TextColumn::make('total_review')->label('Review')->numeric()->color('warning'),
                TextColumn::make('total_warning')->label('Warning')->numeric()->color('danger'),
                TextColumn::make('total_error')->label('Error')->numeric()
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
                SelectFilter::make('mode')->options([
                    'DRY_RUN' => 'Dry Run',
                    'EKSEKUSI' => 'Eksekusi',
                ]),
            ])
            ->defaultSort('id', 'desc');
    }
}
