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

                /*
                |--------------------------------------------------------------------------
                | KURIKULUM
                |--------------------------------------------------------------------------
                */

                TextColumn::make('kurikulum.nama_kurikulum')
                    ->label('Kurikulum')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary')
                    ->description(
                        fn (KurikulumMataKuliah $record): string =>
                            $record->kurikulum?->prodi?->nama_prodi
                            ?? 'Program studi tidak tersedia'
                    )
                    ->icon('heroicon-o-academic-cap')
                    ->iconColor('primary'),

                /*
                |--------------------------------------------------------------------------
                | MATA KULIAH
                |--------------------------------------------------------------------------
                */

                TextColumn::make('mataKuliah.kode_mk')
                    ->label('Mata Kuliah')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Kode mata kuliah disalin')
                    ->copyMessageDuration(1500)
                    ->weight('bold')
                    ->color('gray')
                    ->description(
                        fn (KurikulumMataKuliah $record): string =>
                            $record->mataKuliah?->nama_mk
                            ?? 'Mata kuliah tidak ditemukan'
                    ),

                /*
                |--------------------------------------------------------------------------
                | SEMESTER
                |--------------------------------------------------------------------------
                */

                TextColumn::make('semester_paket')
                    ->label('Semester')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->formatStateUsing(
                        fn ($state): string => "Semester {$state}"
                    )
                    ->color(
                        fn ($state): string => match ((int) $state) {
                            1, 2 => 'info',
                            3, 4 => 'primary',
                            5, 6 => 'warning',
                            7, 8 => 'success',
                            default => 'gray',
                        }
                    ),

                /*
                |--------------------------------------------------------------------------
                | TOTAL SKS
                |--------------------------------------------------------------------------
                */

                TextColumn::make('total_sks')
                    ->label('Beban')
                    ->state(
                        fn (KurikulumMataKuliah $record): int =>
                            (int) $record->sks_tatap_muka
                            + (int) $record->sks_praktek
                            + (int) $record->sks_lapangan
                    )
                    ->suffix(' SKS')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('success')
                    ->description(
                        fn (KurikulumMataKuliah $record): string =>
                            sprintf(
                                'TM %d · P %d · L %d',
                                $record->sks_tatap_muka,
                                $record->sks_praktek,
                                $record->sks_lapangan,
                            )
                    ),

                /*
                |--------------------------------------------------------------------------
                | SIFAT MK
                |--------------------------------------------------------------------------
                */

                TextColumn::make('sifat_mk')
                    ->label('Sifat')
                    ->alignCenter()
                    ->badge()
                    ->formatStateUsing(
                        fn (string $state): string => match ($state) {
                            'W' => 'Wajib',
                            'P' => 'Pilihan',
                            default => $state,
                        }
                    )
                    ->color(
                        fn (string $state): string => match ($state) {
                            'W' => 'primary',
                            'P' => 'warning',
                            default => 'gray',
                        }
                    )
                    ->icon(
                        fn (string $state): string => match ($state) {
                            'W' => 'heroicon-o-check-circle',
                            'P' => 'heroicon-o-adjustments-horizontal',
                            default => 'heroicon-o-question-mark-circle',
                        }
                    ),

                /*
                |--------------------------------------------------------------------------
                | PRASYARAT
                |--------------------------------------------------------------------------
                */

                TextColumn::make('syarat_prasyarat_count')
                    ->label('Prasyarat')
                    ->counts('syaratPrasyarat')
                    ->alignCenter()
                    ->badge()
                    ->formatStateUsing(
                        fn (int $state): string =>
                            $state > 0
                                ? "{$state} MK"
                                : 'Tidak ada'
                    )
                    ->color(
                        fn (int $state): string =>
                            $state > 0 ? 'warning' : 'gray'
                    )
                    ->icon(
                        fn (int $state): string =>
                            $state > 0
                                ? 'heroicon-o-link'
                                : 'heroicon-o-minus'
                    ),

                /*
                |--------------------------------------------------------------------------
                | CREATED
                |--------------------------------------------------------------------------
                */

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                /*
                |--------------------------------------------------------------------------
                | UPDATED
                |--------------------------------------------------------------------------
                */

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            /*
            |--------------------------------------------------------------------------
            | FILTER
            |--------------------------------------------------------------------------
            */

            ->filters([

                SelectFilter::make('kurikulum_id')
                    ->label('Kurikulum')
                    ->relationship(
                        name: 'kurikulum',
                        titleAttribute: 'nama_kurikulum',
                        modifyQueryUsing: function ($query) {
                            $query->whereIn(
                                'prodi_id',
                                app(OrganizationResolver::class)
                                    ->accessibleProdiIds(auth()->user())
                            );
                        },
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make('semester_paket')
                    ->label('Semester')
                    ->options([
                        1 => 'Semester 1',
                        2 => 'Semester 2',
                        3 => 'Semester 3',
                        4 => 'Semester 4',
                        5 => 'Semester 5',
                        6 => 'Semester 6',
                        7 => 'Semester 7',
                        8 => 'Semester 8',
                    ])
                    ->multiple(),

                SelectFilter::make('sifat_mk')
                    ->label('Sifat Mata Kuliah')
                    ->options([
                        'W' => 'Wajib',
                        'P' => 'Pilihan',
                    ]),

                SelectFilter::make('prasyarat')
                    ->label('Prasyarat')
                    ->options([
                        'ada' => 'Memiliki Prasyarat',
                        'tidak_ada' => 'Tanpa Prasyarat',
                    ])
                    ->query(function ($query, array $data) {
                        $value = $data['value'] ?? null;

                        if ($value === 'ada') {
                            $query->has('syaratPrasyarat');
                        }

                        if ($value === 'tidak_ada') {
                            $query->doesntHave('syaratPrasyarat');
                        }
                    }),
            ])

            /*
            |--------------------------------------------------------------------------
            | GROUPING
            |--------------------------------------------------------------------------
            */

            ->defaultGroup('kurikulum.nama_kurikulum')

            /*
            |--------------------------------------------------------------------------
            | SORTING
            |--------------------------------------------------------------------------
            */

            ->defaultSort('semester_paket', 'asc')

            /*
            |--------------------------------------------------------------------------
            | ACTION
            |--------------------------------------------------------------------------
            */

            ->recordActions([
                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square'),
            ])

            /*
            |--------------------------------------------------------------------------
            | BULK ACTION
            |--------------------------------------------------------------------------
            */

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])

            /*
            |--------------------------------------------------------------------------
            | VISUAL
            |--------------------------------------------------------------------------
            */

            ->striped()
            ->paginated([25, 50, 100]);
    }
}