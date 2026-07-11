<?php

namespace App\Filament\Resources\JadwalKuliahs\Tables;

use App\Models\JadwalKuliah;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class JadwalKuliahsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mataKuliah.nama_mk')
                    ->label('Mata Kuliah')
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->weight('bold'),

                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('hari')
                    ->label('Jadwal')
                    ->sortable()
                    ->description(
                        fn(JadwalKuliah $record): string => ($record->jam_mulai ? date('H:i', strtotime($record->jam_mulai)) : '') . ' - ' .
                            ($record->jam_selesai ? date('H:i', strtotime($record->jam_selesai)) : '')
                    ),

                TextColumn::make('ruang.nama_ruang')
                    ->label('Ruangan')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('dosenPengajars.dosen.person.nama_lengkap')
                    ->label('Dosen Pengajar')
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->searchable(),

                TextColumn::make('isi_kelas')
                    ->label('Kapasitas')
                    ->alignCenter()
                    ->formatStateUsing(fn(int $state, JadwalKuliah $record): string => $state . ' / ' . $record->kuota_kelas)
                    ->badge()
                    ->color(fn(int $state, JadwalKuliah $record): string => $state >= $record->kuota_kelas ? 'danger' : 'success'),
            ])
            ->filters([
                TrashedFilter::make()
                    ->native(false),

                SelectFilter::make('tahun_akademik_id')
                    ->label('Tahun Akademik')
                    ->relationship('tahunAkademik', 'nama_tahun')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('hari')
                    ->options([
                        'Senin' => 'Senin',
                        'Selasa' => 'Selasa',
                        'Rabu' => 'Rabu',
                        'Kamis' => 'Kamis',
                        'Jumat' => 'Jumat',
                        'Sabtu' => 'Sabtu',
                        'Minggu' => 'Minggu',
                    ])
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])->defaultSort('created_at', 'desc');
    }
}
