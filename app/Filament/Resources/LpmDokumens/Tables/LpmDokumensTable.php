<?php

namespace App\Filament\Resources\LpmDokumens\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LpmDokumensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_dokumen')->label('Kode')->searchable(),
                TextColumn::make('nama_dokumen')->label('Nama Dokumen')->searchable()->wrap(),
                TextColumn::make('jenis')->label('Jenis')->badge(),
                TextColumn::make('versi')->label('Versi'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'PUBLISHED' => 'success',
                        'REVIEW' => 'warning',
                        'ARCHIVED' => 'gray',
                        default => 'info',
                    }),
                TextColumn::make('unitKerja.nama_unit')->label('Unit Pemilik')->toggleable(),
                TextColumn::make('tgl_berlaku')->label('Berlaku')->date('d/m/Y'),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->filters([
                SelectFilter::make('jenis')
                    ->options([
                        'KEBIJAKAN' => 'Kebijakan Mutu',
                        'MANUAL' => 'Manual Mutu',
                        'STANDAR' => 'Standar Mutu',
                        'FORMULIR' => 'Formulir',
                        'SOP' => 'SOP',
                        'DOKUMEN_PENDUKUNG' => 'Dokumen Pendukung',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'DRAFT' => 'Draft',
                        'REVIEW' => 'Review',
                        'PUBLISHED' => 'Disahkan',
                        'ARCHIVED' => 'Kadaluarsa',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])->defaultSort('nama_dokumen');
    }
}
