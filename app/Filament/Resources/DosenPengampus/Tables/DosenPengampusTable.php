<?php

namespace App\Filament\Resources\DosenPengampus\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Models\DosenPengampu;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction as ActionsEditAction;

class DosenPengampusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc') // Mengurutkan dari data yang paling baru diinput
            ->columns([
                TextColumn::make('tahunAkademik.nama_tahun')
                    ->label('Tahun Akademik')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Disembunyikan default agar tabel tidak terlalu lebar

                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas & Prodi')
                    ->sortable()
                    ->searchable()
                    ->weight('bold')
                    // Menampilkan Nama Prodi dan Angkatan di bawah Nama Kelas
                    ->description(
                        fn(DosenPengampu $record): string => ($record->kelas?->prodi?->nama_prodi ?? 'Prodi Unknown') .
                            ' (Angkatan: ' . ($record->kelas?->angkatan?->id_tahun ?? '-') . ')'
                    ),

                TextColumn::make('mataKuliah.nama_mk')
                    ->label('Mata Kuliah')
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    // Menampilkan Kode MK dan SKS di bawah Nama Mata Kuliah
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
                    ->trueIcon('heroicon-s-star')      // Ikon bintang solid jika koordinator
                    ->falseIcon('heroicon-o-minus')    // Ikon minus jika dosen anggota biasa
                    ->trueColor('warning')             // Warna emas/kuning
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
            ])
            ->filters([
                SelectFilter::make('tahun_akademik_id')
                    ->relationship('tahunAkademik', 'nama_tahun')
                    ->label('Tahun Akademik')
                    ->preload(),

                // --- FILTER ANGKATAN (Mencari menembus relasi Kelas) ---
                SelectFilter::make('angkatan')
                    ->label('Filter Angkatan')
                    ->options(fn() => \App\Models\RefAngkatan::orderBy('id_tahun', 'desc')->pluck('id_tahun', 'id_tahun'))
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            // Mencari dosen pengampu yang mengajar di kelas dengan angkatan yang dipilih
                            $query->whereHas('kelas', function ($q) use ($data) {
                                $q->where('angkatan_id', $data['value']);
                            });
                        }
                    })
                    ->searchable()
                    ->preload(),
                // --------------------------------------------------------

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
                ActionsEditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
