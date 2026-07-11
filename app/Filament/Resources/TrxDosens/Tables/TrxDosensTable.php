<?php

namespace App\Filament\Resources\TrxDosens\Tables;

use App\Models\RefGelar;
use App\Models\TrxDosen;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TrxDosensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('person.nama_dengan_gelar')
                    ->label('Nama Dosen')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nidn')
                    ->label('NIDN')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nuptk')
                    ->label('NUPTK')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('prodi.nama_prodi')
                    ->label('Program Studi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('jenis_dosen')
                    ->label('Jenis Dosen')
                    ->badge()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('person.email')
                    ->label('Email')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('person.no_hp')
                    ->label('No HP')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->relationship('prodi', 'nama_prodi')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('jenis_dosen')
                    ->label('Jenis Dosen')
                    ->options([
                        'TETAP' => 'Dosen Tetap',
                        'LB' => 'Dosen Luar Biasa',
                        'PRAKTISI' => 'Praktisi',
                        'TIDAK_TETAP' => 'Tidak Tetap',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),

                Filter::make('memiliki_nidn')
                    ->label('Memiliki NIDN')
                    ->toggle()
                    ->query(fn($query) => $query->whereNotNull('nidn')),

                Filter::make('memiliki_nuptk')
                    ->label('Memiliki NUPTK')
                    ->toggle()
                    ->query(fn($query) => $query->whereNotNull('nuptk')),

                Filter::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->schema([
                        DatePicker::make('dari'),
                        DatePicker::make('sampai'),
                    ])
                    ->query(function ($query, array $data) {

                        return $query
                            ->when(
                                $data['dari'],
                                fn($q, $date) => $q->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['sampai'],
                                fn($q, $date) => $q->whereDate('created_at', '<=', $date)
                            );
                    }),
                TrashedFilter::make(),
            ])->filtersFormColumns(2)
            ->recordActions([
                EditAction::make(),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
