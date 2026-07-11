<?php

namespace App\Filament\Resources\RefProdis\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class RefProdisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fakultas.nama_fakultas')
                    ->label('Fakultas')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('kode_prodi_internal')
                    ->label('Kode Internal')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('nama_prodi')
                    ->label('Nama Prodi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('jenjang')
                    ->label('Jenjang')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'S1', 'D4' => 'success',
                        'S2' => 'info',
                        'S3' => 'primary',
                        'D3' => 'warning',
                        'PROFESI' => 'danger',
                        default => 'gray',
                    }),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable()
                    ->action(function ($record, $column) {
                        $name = $column->getName();
                        $record->update([
                            $name => !$record->$name
                        ]);
                    })
                    ->tooltip('Klik untuk mengubah status'),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('fakultas_id')
                    ->label('Fakultas')
                    ->relationship('fakultas', 'nama_fakultas')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('jenjang')
                    ->options([
                        'D3' => 'D3',
                        'D4' => 'D4',
                        'S1' => 'S1',
                        'S2' => 'S2',
                        'S3' => 'S3',
                        'PROFESI' => 'PROFESI',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
