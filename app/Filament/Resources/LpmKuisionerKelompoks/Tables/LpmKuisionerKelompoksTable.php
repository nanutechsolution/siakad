<?php

namespace App\Filament\Resources\LpmKuisionerKelompoks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LpmKuisionerKelompoksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_kelompok')->label('Nama Kelompok')->searchable(),
                TextColumn::make('kategori')->label('Kategori')->badge(),
                TextColumn::make('tahunAkademik.nama_tahun')->label('Tahun Akademik'),
                TextColumn::make('pertanyaans_count')->label('Jumlah Pertanyaan')->counts('pertanyaans'),
                TextColumn::make('urutan')->label('Urutan')->sortable(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->filters([
                SelectFilter::make('kategori')
                    ->options([
                        'EDOM' => 'EDOM',
                        'MONEV' => 'Monev',
                        'KEPUASAN_MAHASISWA' => 'Kepuasan Mahasiswa',
                        'KEPUASAN_DOSEN' => 'Kepuasan Dosen',
                        'KEPUASAN_TENDIK' => 'Kepuasan Tendik',
                        'KEPUASAN_ALUMNI' => 'Kepuasan Alumni',
                        'KEPUASAN_PENGGUNA_LULUSAN' => 'Kepuasan Pengguna Lulusan',
                    ]),
            ])
            ->defaultSort('urutan')
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
