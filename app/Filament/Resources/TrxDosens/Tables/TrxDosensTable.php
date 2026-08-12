<?php

namespace App\Filament\Resources\TrxDosens\Tables;

use App\Domain\Authorization\Services\FormResolver;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
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

                TextColumn::make('person.nama_lengkap')
                    ->label('Nama Dosen')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($record) => $record->person?->nama_dengan_gelar ?? '-'),

                TextColumn::make('nidn')
                    ->label('NIDN')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('NIDN disalin')
                    ->copyMessageDuration(1500)
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-o-identification'),
                TextColumn::make('nuptk')
                    ->label('NUPTK')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('NUPTK disalin')
                    ->copyMessageDuration(1500)
                    ->badge()
                    ->color('gray')
                    ->icon('heroicon-o-credit-card')
                    ->toggleable(),

                TextColumn::make('prodi.nama_prodi')
                    ->label('Program Studi')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->wrap(),

                TextColumn::make('jenis_dosen')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'TETAP' => 'Dosen Tetap',
                        'TIDAK_TETAP' => 'Tidak Tetap',
                        'LB' => 'Dosen Luar Biasa',
                        'PRAKTISI' => 'Praktisi',
                        default => $state ?? '—',
                    })
                    ->color(fn(?string $state): string => match ($state) {
                        'TETAP' => 'success',
                        'TIDAK_TETAP' => 'warning',
                        'LB' => 'info',
                        'PRAKTISI' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(
                        fn($record) =>
                        $record->is_active
                            ? 'Dosen Aktif'
                            : 'Dosen Tidak Aktif'
                    ),

                TextColumn::make('person.email')
                    ->label('Email')
                    ->icon('heroicon-o-envelope')
                    ->copyable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('person.no_hp')
                    ->label('No. HP')
                    ->icon('heroicon-o-phone')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->searchable()
                    ->options(fn() => app(FormResolver::class)->prodiOptions(auth()->user()))
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
