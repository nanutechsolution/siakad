<?php

namespace App\Filament\Resources\LpmUnitKerjas\Tables;

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

class LpmUnitKerjasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_unit')->label('Kode')->searchable(),
                TextColumn::make('nama_unit')->label('Nama Unit')->searchable(),
                TextColumn::make('jenis_unit')->label('Jenis')->badge(),
                TextColumn::make('parent.nama_unit')->label('Unit Induk')->toggleable(),
                TextColumn::make('kepalaUnit.nama_lengkap')->label('Kepala Unit')->toggleable(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->filters([
                SelectFilter::make('jenis_unit')
                    ->label('Jenis Unit')
                    ->options([
                        'UNIVERSITAS' => 'Universitas',
                        'FAKULTAS' => 'Fakultas',
                        'PRODI' => 'Program Studi',
                        'LEMBAGA' => 'Lembaga',
                        'BIRO' => 'Biro',
                        'UPT' => 'UPT',
                    ]),
                TernaryFilter::make('is_active')->label('Status Aktif'),
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
            ])->defaultSort('nama_unit');
    }
}
