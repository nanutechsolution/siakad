<?php

namespace App\Filament\Resources\LpmAkreditasis\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LpmAkreditasisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lembaga.nama')->label('Lembaga')->searchable(),
                TextColumn::make('jenis_akreditasi')->label('Jenis')->badge(),
                TextColumn::make('prodi.nama_prodi')->label('Prodi')->default('Institusi')->toggleable(),
                TextColumn::make('instrumen')->label('Instrumen'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'SELESAI' => 'success',
                        'VISITASI' => 'warning',
                        'SUBMIT' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('peringkat_target')->label('Target')->toggleable(),
                TextColumn::make('peringkat_hasil')->label('Hasil')->toggleable(),
                TextColumn::make('berlaku_sampai')->label('Berlaku Sampai')->date('d/m/Y'),
            ])
            ->filters([
                SelectFilter::make('lembaga_id')->label('Lembaga')->relationship('lembaga', 'nama'),
                SelectFilter::make('status')
                    ->options([
                        'PERSIAPAN' => 'Persiapan',
                        'PENGISIAN' => 'Pengisian Borang',
                        'SUBMIT' => 'Sudah Submit',
                        'VISITASI' => 'Visitasi',
                        'SELESAI' => 'Selesai',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])->defaultSort('created_at', 'desc');
    }
}
