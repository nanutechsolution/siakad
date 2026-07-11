<?php

namespace App\Filament\Resources\KeuanganSkemaTarifs\Tables;

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

class KeuanganSkemaTarifsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('angkatan_id', 'desc')

            ->columns([

                TextColumn::make('nama_skema')
                    ->label('Nama Skema')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('angkatan.id_tahun')
                    ->label('Angkatan')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('prodi.nama_prodi')
                    ->label('Program Studi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('programKelas.nama_program')
                    ->label('Program Kelas')
                    ->badge()
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

            ])

            ->filters([

                SelectFilter::make('angkatan')
                    ->relationship('angkatan', 'id_tahun'),

                SelectFilter::make('prodi')
                    ->relationship('prodi', 'nama_prodi'),

                SelectFilter::make('programKelas')
                    ->relationship('programKelas', 'nama_program'),

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
