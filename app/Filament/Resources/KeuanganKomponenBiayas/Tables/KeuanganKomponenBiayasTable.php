<?php

namespace App\Filament\Resources\KeuanganKomponenBiayas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class KeuanganKomponenBiayasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('urutan_prioritas')

            ->columns([

                TextColumn::make('nama_komponen')
                    ->label('Komponen Biaya')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('tipe_biaya')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'TETAP' => 'primary',
                        'SKS' => 'success',
                        'SEKALI' => 'warning',
                        'INSIDENTAL' => 'danger',
                    })
                    ->sortable(),

                TextColumn::make('urutan_prioritas')
                    ->label('Prioritas')
                    ->alignCenter()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('Dihapus')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])

            ->filters([

                SelectFilter::make('tipe_biaya')
                    ->label('Tipe Biaya')
                    ->options([
                        'TETAP' => 'Tetap',
                        'SKS' => 'Per SKS',
                        'SEKALI' => 'Sekali',
                        'INSIDENTAL' => 'Insidental',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Status'),

                TrashedFilter::make(),

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
