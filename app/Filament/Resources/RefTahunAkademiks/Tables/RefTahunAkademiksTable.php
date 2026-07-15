<?php

namespace App\Filament\Resources\RefTahunAkademiks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RefTahunAkademiksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('kode_tahun', 'desc')
            ->columns([
                TextColumn::make('kode_tahun')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama_tahun')
                    ->label('Nama Tahun')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('semester')
                    ->label('Semester')
                    ->formatStateUsing(fn(int $state): string => match ($state) {
                        1 => 'Ganjil',
                        2 => 'Genap',
                        3 => 'Pendek',
                        default => '-',
                    })
                    ->badge()
                    ->color(fn(int $state): string => match ($state) {
                        1 => 'info',
                        2 => 'success',
                        3 => 'warning',
                        default => 'gray',
                    }),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('buka_krs')
                    ->label('Buka KRS')
                    ->boolean()
                    ->toggleable(),

                IconColumn::make('buka_input_nilai')
                    ->label('Buka Nilai')
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('tanggal_mulai')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('semester')
                    ->options([
                        1 => 'Ganjil',
                        2 => 'Genap',
                        3 => 'Pendek',
                    ])
                    ->native(false),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->native(false),
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
