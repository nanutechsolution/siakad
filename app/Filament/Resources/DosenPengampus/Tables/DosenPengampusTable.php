<?php

namespace App\Filament\Resources\DosenPengampus\Tables;

use App\Models\DosenPengampu;
use App\Models\RefAngkatan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction as ActionsEditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DosenPengampusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // FIX: eager load semua relasi yang dipakai kolom — sebelumnya N+1 query per baris
            ->modifyQueryUsing(fn(Builder $query) => $query->with([
                'tahunAkademik',
                'kelas.prodi',
                'kelas.angkatan',
                'mataKuliah',
                'dosen.person',
                'ruang',
            ]))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('tahunAkademik.nama_tahun')
                    ->label('Tahun Akademik')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas & Prodi')
                    ->sortable()
                    ->searchable()
                    ->weight('bold')
                    ->description(
                        fn(DosenPengampu $record): string => ($record->kelas?->prodi?->nama_prodi ?? 'Prodi Unknown') .
                            ' (Angkatan: ' . ($record->kelas?->angkatan?->id_tahun ?? '-') . ')'
                    ),

                TextColumn::make('mataKuliah.nama_mk')
                    ->label('Mata Kuliah')
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->description(
                        fn(DosenPengampu $record): string => ($record->mataKuliah?->kode_mk ?? '-') . ' • ' .
                            ($record->mataKuliah?->sks_default ?? '0') . ' SKS'
                    ),

                TextColumn::make('dosen.person.nama_lengkap')
                    ->label('Dosen Pengampu')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-user-circle')
                    ->color('primary'),

                IconColumn::make('is_koordinator')
                    ->label('Koordinator')
                    ->boolean()
                    ->trueIcon('heroicon-s-star')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->tooltip('Koordinator bertugas menginput nilai akhir mahasiswa.')
                    ->alignCenter(),

                TextColumn::make('ruang.nama_ruang')
                    ->label('Wajib Ruang')
                    ->badge()
                    ->color(fn($state) => $state ? 'danger' : 'success')
                    ->formatStateUsing(fn($state) => $state ?? 'Otomatis (Mesin)')
                    ->icon(fn($state) => $state ? 'heroicon-o-lock-closed' : 'heroicon-o-sparkles')
                    ->tooltip('Apakah MK ini dikunci pada Lab/Ruangan spesifik?')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Diinput Pada')
                    ->since()
                    ->tooltip(fn(DosenPengampu $record) => $record->created_at?->format('d M Y, H:i'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tahun_akademik_id')
                    ->relationship('tahunAkademik', 'nama_tahun')
                    ->label('Tahun Akademik')
                    ->preload(),

                SelectFilter::make('angkatan')
                    ->label('Filter Angkatan')
                    ->options(fn() => RefAngkatan::orderBy('id_tahun', 'desc')->pluck('id_tahun', 'id_tahun'))
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('kelas', function ($q) use ($data) {
                                $q->where('angkatan_id', $data['value']);
                            });
                        }
                    })
                    ->searchable()
                    ->preload(),

                SelectFilter::make('kelas_id')
                    ->relationship('kelas', 'nama_kelas')
                    ->label('Filter Kelas Spesifik')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_koordinator')
                    ->label('Status Koordinator')
                    ->placeholder('Semua Dosen')
                    ->trueLabel('Hanya Koordinator MK')
                    ->falseLabel('Hanya Anggota Tim'),
            ])
            ->recordActions([
                ViewAction::make(),
                ActionsEditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Dosen Pengampu')
            ->emptyStateDescription('Belum ada penugasan dosen pengampu yang tercatat. Klik "Buat" untuk menambahkan.')
            ->emptyStateIcon('heroicon-o-user-group')
            ->striped();
    }
}
