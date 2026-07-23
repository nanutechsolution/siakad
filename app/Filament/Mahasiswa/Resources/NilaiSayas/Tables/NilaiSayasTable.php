<?php

namespace App\Filament\Mahasiswa\Resources\NilaiSayas\Tables;

use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NilaiSayasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mataKuliah.nama_mk')
                    ->label('Mata Kuliah')
                    ->searchable()
                    ->weight('bold')
                    ->wrap()
                    ->description(fn($record) => $record->mataKuliah?->kode_mk),

                TextColumn::make('mataKuliah.sks_default')
                    ->label('SKS')
                    ->badge()
                    ->alignCenter()
                    ->grow(false),

                TextColumn::make('nilai_huruf')
                    ->label('Nilai')
                    ->badge()
                    ->alignCenter()
                    ->color(fn(?string $state) => match ($state) {
                        'A', 'A-' => 'success',
                        'B+', 'B', 'B-' => 'info',
                        'C+', 'C' => 'warning',
                        default => 'danger',
                    }),

                TextColumn::make('nilai_angka')
                    ->label('Angka')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('nilai_indeks')
                    ->label('Bobot')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('krs.tahunAkademik.nama_tahun')
                    ->label('Tahun Akademik')
                    ->description(fn($record) => match ($record->krs?->tahunAkademik?->semester) {
                        1 => 'Semester Ganjil',
                        2 => 'Semester Genap',
                        3 => 'Semester Pendek',
                        default => '-',
                    }),

                TextColumn::make('jadwalKuliah.dosenPengampu')
                    ->label('Dosen Pengampu')
                    ->wrap()
                    ->formatStateUsing(function ($state, $record) {
                        $dosen = $record->jadwalKuliah?->dosenPengampu
                            ?->sortByDesc(fn($item) => $item->pivot?->is_koordinator ?? false)
                            ->first();

                        return $dosen?->person?->nama_lengkap ?? '-';
                    })
                    ->visibleFrom('md'),
            ])
            ->filters([
                SelectFilter::make('tahun_akademik_id')
                    ->label('Tahun Akademik')
                    ->relationship('krs.tahunAkademik', 'nama_tahun'),
            ])
            ->recordActions([
                Action::make('detailKomponen')
                    ->label('Detail')
                    ->icon('heroicon-o-list-bullet')
                    ->modalHeading(fn($record) => 'Rincian Nilai - ' . $record->mataKuliah?->nama_mk)
                    ->modalContent(fn($record) => view(
                        'filament.mahasiswa.partials.detail-komponen-nilai',
                        ['record' => $record]
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
            ]);
    }
}
