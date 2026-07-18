<?php

namespace App\Filament\Resources\KurikulumMataKuliahs\Tables;

use App\Domain\Authorization\Services\OrganizationResolver;
use App\Models\KurikulumMataKuliah;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class KurikulumMataKuliahsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kurikulum.nama_kurikulum')
                    ->label('Kurikulum')
                    ->sortable()
                    ->searchable()
                    ->description(fn(KurikulumMataKuliah $record): string => 'Semester ' . $record->semester_paket),

                TextColumn::make('mataKuliah.kode_mk')
                    ->label('Kode')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('mataKuliah.nama_mk')
                    ->label('Mata Kuliah')
                    ->sortable()
                    ->searchable()
                    ->wrap(),

                TextColumn::make('semester_paket')
                    ->label('Smt')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('sifat_mk')
                    ->label('Sifat')
                    ->formatStateUsing(fn(string $state): string => $state === 'W' ? 'Wajib' : 'Pilihan')
                    ->badge()
                    ->color(fn(string $state): string => $state === 'W' ? 'primary' : 'gray'),

                TextColumn::make('syarat_prasyarat_count')
                    ->label('Prasyarat')
                    ->counts('syaratPrasyarat')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('kurikulum_id')
                    ->label('Filter Kurikulum')
                    ->relationship(
                        name: 'kurikulum',
                        titleAttribute: 'nama_kurikulum',
                        modifyQueryUsing: fn($query) => $query->whereIn(
                            'prodi_id',
                            app(OrganizationResolver::class)->accessibleProdiIds(auth()->user()),
                        ),
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make('semester_paket')
                    ->label('Filter Semester')
                    ->options([
                        1 => 'Semester 1',
                        2 => 'Semester 2',
                        3 => 'Semester 3',
                        4 => 'Semester 4',
                        5 => 'Semester 5',
                        6 => 'Semester 6',
                        7 => 'Semester 7',
                        8 => 'Semester 8',
                    ]),
            ])->defaultGroup('kurikulum.nama_kurikulum')
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
