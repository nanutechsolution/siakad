<?php

namespace App\Filament\Resources\TahunAkademiks\Tables;

use App\Enums\TahunAkademikStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TahunAkademiksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('kode_tahun', 'desc')
            ->columns([
                Stack::make([
                    TextColumn::make('kode_tahun')
                        ->label('Semester')
                        ->weight('bold')
                        ->searchable(),
                    TextColumn::make('nama_tahun')
                        ->color('gray')
                        ->size('sm')
                        ->searchable(),
                ]),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                ViewColumn::make('progress')
                    ->label('Progress')
                    ->view('filament.tables.columns.progress-bar'),

                TextColumn::make('mahasiswa_aktif')
                    ->label('Mahasiswa')
                    ->getStateUsing(fn($record) => number_format($record->statistik()['mahasiswa_aktif'] ?? 0))
                    ->alignEnd(),

                TextColumn::make('nilai_masuk')
                    ->label('Nilai Masuk')
                    ->getStateUsing(fn($record) => ($record->statistik()['persen_nilai_masuk'] ?? 0) . '%')
                    ->badge()
                    ->color(fn($record) => ($record->statistik()['persen_nilai_masuk'] ?? 0) >= 90 ? 'success' : 'warning')
                    ->alignEnd(),

                TextColumn::make('tanggal_mulai')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(TahunAkademikStatus::cases())->mapWithKeys(
                        fn(TahunAkademikStatus $s) => [$s->value => $s->getLabel()]
                    ))
                    ->native(false),

                SelectFilter::make('semester')
                    ->options([1 => 'Ganjil', 2 => 'Genap', 3 => 'Pendek'])
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
