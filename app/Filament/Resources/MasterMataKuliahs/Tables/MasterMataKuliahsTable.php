<?php

declare(strict_types=1);

namespace App\Filament\Resources\MasterMataKuliahs\Tables;

use App\Models\RefFakultas;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Grid as FormGrid;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MasterMataKuliahsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('prodi.nama_prodi')
                    ->label('Program Studi')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('kode_mk')
                    ->label('Kode MK')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('nama_mk')
                    ->label('Nama Mata Kuliah')
                    ->sortable()
                    ->searchable()
                    ->wrap(),

                TextColumn::make('sks_default')
                    ->label('SKS')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('jenis_mk')
                    ->label('Jenis')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'A' => 'Wajib',
                        'B' => 'Pilihan',
                        'C' => 'Wajib Peminatan',
                        'D' => 'Pilihan Peminatan',
                        'S' => 'Skripsi/TA',
                        default => $state,
                    })
                    ->badge()
                    ->color('gray'),

                TextColumn::make('activity_type')
                    ->label('Aktivitas')
                    ->searchable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'MBKM' => 'success',
                        'PRAKTIK KERJA' => 'warning',
                        default => 'gray',
                    })
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->native(false),

                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->relationship('prodi', 'nama_prodi')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Filter::make('fakultas_id')
                    ->label('Fakultas')
                    ->schema([
                        \Filament\Forms\Components\Select::make('fakultas_id')
                            ->label('Fakultas')
                            ->options(fn(): array => RefFakultas::query()
                                ->orderBy('nama_fakultas')
                                ->pluck('nama_fakultas', 'id')
                                ->toArray())
                            ->searchable()
                            ->preload(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['fakultas_id'] ?? null,
                            fn(Builder $q, $fakultasId) => $q->whereHas(
                                'prodi',
                                fn(Builder $prodiQuery) => $prodiQuery->where('fakultas_id', $fakultasId)
                            )
                        );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (blank($data['fakultas_id'] ?? null)) {
                            return null;
                        }

                        $nama = RefFakultas::query()->find($data['fakultas_id'])?->nama_fakultas;

                        return $nama ? "Fakultas: {$nama}" : null;
                    }),

                SelectFilter::make('jenis_mk')
                    ->label('Jenis Mata Kuliah')
                    ->options([
                        'A' => 'Wajib',
                        'B' => 'Pilihan',
                        'C' => 'Wajib Peminatan',
                        'D' => 'Pilihan Peminatan',
                        'S' => 'Tugas Akhir/Skripsi',
                    ])
                    ->multiple(),

                SelectFilter::make('activity_type')
                    ->label('Tipe Aktivitas')
                    ->options([
                        'REGULAR' => 'REGULAR',
                        'MBKM' => 'MBKM',
                        'PRAKTIK KERJA' => 'PRAKTIK KERJA',
                    ])
                    ->multiple(),

                Filter::make('sks_default_range')
                    ->label('Rentang Total SKS')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('sks_dari')
                                    ->label('Dari')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(6),
                                TextInput::make('sks_sampai')
                                    ->label('Sampai')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(6),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['sks_dari'] ?? null,
                                fn(Builder $q, $value) => $q->where('sks_default', '>=', (int) $value)
                            )
                            ->when(
                                $data['sks_sampai'] ?? null,
                                fn(Builder $q, $value) => $q->where('sks_default', '<=', (int) $value)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['sks_dari'] ?? null)) {
                            $indicators[] = 'SKS ≥ ' . $data['sks_dari'];
                        }

                        if (filled($data['sks_sampai'] ?? null)) {
                            $indicators[] = 'SKS ≤ ' . $data['sks_sampai'];
                        }

                        return $indicators;
                    }),

                Filter::make('sks_tidak_seimbang')
                    ->label('Rincian SKS Tidak Seimbang')
                    ->toggle()
                    ->query(fn(Builder $query): Builder => $query->whereRaw(
                        '(sks_tatap_muka + sks_praktek + sks_lapangan) != sks_default'
                    )),

                Filter::make('belum_ada_rincian_sks')
                    ->label('Belum Ada Rincian SKS')
                    ->toggle()
                    ->query(fn(Builder $query): Builder => $query
                        ->where('sks_tatap_muka', 0)
                        ->where('sks_praktek', 0)
                        ->where('sks_lapangan', 0)),
            ])
            ->filtersFormColumns(2)
            ->deferFilters()
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('nama_mk');
    }
}
