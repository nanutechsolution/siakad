<?php

namespace App\Filament\Clusters\Migration\Resources\MigrationHistories\Tables;

use App\Filament\Clusters\Migration\Resources\MigrationHistories\Pages\ViewMigrationHistory;
use App\Models\MigrationBatch;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MigrationHistoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('source')->label('Sumber')->badge()
                    ->formatStateUsing(fn($state) => $state->label()),
                TextColumn::make('status')->label('Status')->badge()
                    ->color(fn($state) => $state->color())
                    ->formatStateUsing(fn($state) => $state->label()),
                TextColumn::make('file_name')->label('Nama File')->limit(30),
                TextColumn::make('total_rows')->label('Total')->sortable(),
                TextColumn::make('total_berhasil')->label('Berhasil')->color('success')->sortable(),
                TextColumn::make('total_gagal')->label('Gagal')->color('danger')->sortable(),
                TextColumn::make('total_dilewati')->label('Dilewati')->color('warning')->sortable(),
                TextColumn::make('creator.name')->label('Operator'),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordUrl(fn(MigrationBatch $record): string => ViewMigrationHistory::getUrl([$record->id]));
    }
}
