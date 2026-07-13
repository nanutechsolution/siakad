<?php

namespace App\Filament\Dosen\Resources\JadwalMengajars\Tables;

use App\Models\JadwalKuliah;
use App\Models\RefTahunAkademik;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class JadwalMengajarsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('hari')
                    ->label('Hari')
                    ->badge()
                    ->sortable(),

                TextColumn::make('jam')
                    ->label('Jam')
                    ->state(fn(JadwalKuliah $record) => sprintf(
                        '%s - %s',
                        $record->jam_mulai ? Carbon::parse($record->jam_mulai)->format('H:i') : '-',
                        $record->jam_selesai ? Carbon::parse($record->jam_selesai)->format('H:i') : '-',
                    )),

                TextColumn::make('mataKuliah.nama_mk')
                    ->label('Mata Kuliah')
                    ->description(fn(JadwalKuliah $record) => $record->mataKuliah?->kode_mk)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ruang.nama_ruang')
                    ->label('Ruang')
                    ->placeholder('Belum ditentukan'),

                TextColumn::make('peran')
                    ->label('Peran')
                    ->state(fn(JadwalKuliah $record) => $record->dosenPengampu->first()?->is_koordinator
                        ? 'Koordinator'
                        : 'Anggota Tim')
                    ->badge()
                    ->color(fn(string $state) => $state === 'Koordinator' ? 'success' : 'gray'),

                TextColumn::make('progress')
                    ->label('Pertemuan')
                    ->state(function (JadwalKuliah $record) {
                        $rencana = $record->dosenPengampu->first()?->rencana_tatap_muka ?? 14;

                        return "{$record->sesi_terlaksana_count} / {$rencana}";
                    })
                    ->badge()
                    ->color(function (JadwalKuliah $record) {
                        $rencana = $record->dosenPengampu->first()?->rencana_tatap_muka ?? 14;

                        return $record->sesi_terlaksana_count >= $rencana ? 'success' : 'warning';
                    }),

                TextColumn::make('isi_kelas')
                    ->label('Peserta')
                    ->state(fn(JadwalKuliah $record) => "{$record->isi_kelas} / {$record->kuota_kelas}"),

                TextColumn::make('tahunAkademik.nama_tahun')
                    ->label('Tahun Akademik')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tahun_akademik_id')
                    ->label('Tahun Akademik')
                    ->relationship('tahunAkademik', 'nama_tahun')   // <-- ini pakai whereHas() di balik layar
                    ->default(fn() => RefTahunAkademik::where('is_active', true)->value('id'))
            ])
            ->defaultSort('jam_mulai')
            ->striped()
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
