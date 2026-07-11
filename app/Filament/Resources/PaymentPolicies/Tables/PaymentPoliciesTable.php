<?php

namespace App\Filament\Resources\PaymentPolicies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PaymentPoliciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Kebijakan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tahunAkademik.nama_tahun')
                    ->label('Periode')
                    ->sortable(),
                TextColumn::make('prodi.nama_prodi')
                    ->label('Prodi')
                    ->default('Semua Prodi')
                    ->sortable(),
                TextColumn::make('programKelas.nama_program')
                    ->label('Program Kelas')
                    ->default('Semua Kelas')
                    ->sortable(),
                IconColumn::make('aktif')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('tahun_akademik_id')
                    ->label('Tahun Akademik')
                    ->relationship('tahunAkademik', 'nama_tahun'),
                TernaryFilter::make('aktif')
                    ->label('Status Aktif'),
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
