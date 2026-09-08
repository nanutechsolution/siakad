<?php

namespace App\Filament\Resources\RefRuangs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RefRuangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // 1. Info Dasar Ruangan
                TextColumn::make('kode_ruang')
                    ->label('Kode')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('nama_ruang')
                    ->label('Nama Ruangan')
                    ->searchable()
                    ->description(fn($record) => $record->kampus?->nama_kampus ?? 'Kampus Belum Diatur') // Info kampus di bawah nama
                    ->wrap(),

                // 2. Kategori Ruangan (Badge Berwarna)
                TextColumn::make('jenis_ruang')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'TEORI' => 'gray',
                        'LABORATORIUM' => 'warning',
                        'STUDIO' => 'info',
                        default => 'primary',
                    })
                    ->sortable(),

                // 3. Kapasitas (Dengan Sufiks)
                TextColumn::make('kapasitas')
                    ->label('Kapasitas')
                    ->sortable()
                    ->color(fn($state) => $state < 20 ? 'danger' : 'success')
                    ->suffix(' Kursi'),

                // 4. Status Kepemilikan (Eksklusif Prodi atau Umum)
                TextColumn::make('prodi.nama_prodi')
                    ->label('Hak Akses')
                    ->default('Umum (Semua Prodi)')
                    ->badge()
                    ->color(fn($record) => $record->prodi_id ? 'primary' : 'success')
                    ->searchable(),

                // 5. Visualisasi Koordinat Simpel
                TextColumn::make('lokasi')
                    ->label('Koordinat GPS')
                    ->state(fn($record) => $record->latitude ? "{$record->latitude}, {$record->longitude}" : 'Belum Ada')
                    ->size('xs')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true), // Sembunyikan default agar tabel tidak sesak

                // 6. Status Aktif
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                // --- TAMBAHAN FILTER BARU ---
                SelectFilter::make('kampus_id')
                    ->label('Filter Kampus')
                    ->relationship('kampus', 'nama_kampus'),

                SelectFilter::make('jenis_ruang')
                    ->label('Filter Jenis Ruang')
                    ->options([
                        'TEORI' => 'Teori',
                        'LABORATORIUM' => 'Laboratorium',
                        'STUDIO' => 'Studio'
                    ]),

                Filter::make('active')
                    ->label('Hanya Ruang Aktif')
                    ->query(fn($query) => $query->where('is_active', true)),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('kode_ruang', 'asc');
    }
}
