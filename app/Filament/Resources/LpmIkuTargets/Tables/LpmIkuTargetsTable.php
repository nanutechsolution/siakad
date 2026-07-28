<?php

namespace App\Filament\Resources\LpmIkuTargets\Tables;

use App\Models\LpmIkuTarget;
use App\Models\RefProdi;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LpmIkuTargetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('indikator.nama_indikator')->label('Indikator')->searchable()->wrap(),
                TextColumn::make('prodi.nama_prodi')->label('Prodi')->toggleable(),
                TextColumn::make('unitKerja.nama_unit')->label('Unit Kerja')->toggleable(),
                TextColumn::make('tahun')->label('Tahun')->sortable(),
                TextColumn::make('target_nilai')->label('Target')->numeric(2),
                TextColumn::make('capaian_nilai')->label('Capaian')->numeric(2),
                TextColumn::make('persen_capaian')
                    ->label('% Capaian')
                    ->state(fn(LpmIkuTarget $record) => $record->target_nilai > 0
                        ? round(((float) $record->capaian_nilai / (float) $record->target_nilai) * 100, 1) . '%'
                        : '-')
                    ->badge()
                    ->color(fn(LpmIkuTarget $record) => $record->target_nilai > 0 && $record->capaian_nilai >= $record->target_nilai ? 'success' : 'danger'),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('buktiPelaksanaans_count')->label('Jumlah Bukti')->counts('buktiPelaksanaans'),
            ])
            ->filters([
                SelectFilter::make('tahun')
                    ->options(fn() => LpmIkuTarget::query()->distinct()->orderByDesc('tahun')->pluck('tahun', 'tahun')),
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->options(fn() => RefProdi::query()->pluck('nama_prodi', 'id')),
                SelectFilter::make('status')
                    ->options([
                        'DRAFT' => 'Draft',
                        'SUBMITTED' => 'Submitted',
                        'VALIDATED' => 'Validated',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])->defaultSort('tahun', 'desc');
    }
}
