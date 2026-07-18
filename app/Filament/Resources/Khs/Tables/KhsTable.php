<?php

namespace App\Filament\Resources\Khs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class KhsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mahasiswa.nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mahasiswa.person.nama_lengkap')
                    ->label('Nama Mahasiswa')
                    ->searchable(),
                TextColumn::make('tahunAkademik.nama_tahun')
                    ->label('Tahun Akademik')
                    ->sortable(),
                TextColumn::make('mahasiswa.prodi.nama_prodi')
                    ->label('Program Studi'),
                TextColumn::make('total_sks_diambil')
                    ->label('Total SKS')
                    ->numeric(),
            ])
            ->filters([
                SelectFilter::make('tahun_akademik_id')
                    ->relationship('tahunAkademik', 'nama_tahun')
                    ->label('Pilih Tahun Akademik'),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
