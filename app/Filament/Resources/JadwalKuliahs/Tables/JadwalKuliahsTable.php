<?php

namespace App\Filament\Resources\JadwalKuliahs\Tables;

use App\Models\JadwalKuliah;
use App\Models\RefTahunAkademik;
use App\Models\TrxDosen;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JadwalKuliahsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query
                    // 1. EAGER LOADING: Mencegah N+1 query agar loading tabel sangat cepat
                    ->with([
                        'mataKuliah',
                        'kelas.prodi',
                        'kelas.angkatan',
                        'ruang',
                        'dosenPengajars.dosen.person'
                    ])
                    ->orderByRaw("FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")
                    ->orderBy('jam_mulai');
            })
            ->columns([
                // 1. MATA KULIAH
                TextColumn::make('mataKuliah.nama_mk')
                    ->label('Mata Kuliah')
                    ->sortable()
                    ->searchable(['nama_mk', 'kode_mk'])
                    ->wrap()
                    ->weight('bold')
                    ->description(
                        fn(JadwalKuliah $record): string => ($record->mataKuliah->kode_mk ?? '-') . ' • ' .
                            ($record->mataKuliah->sks_default ?? 0) . ' SKS'
                    ),

                // 2. KELAS, ANGKATAN, & PRODI
                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas & Angkatan')
                    ->sortable()
                    ->searchable()
                    ->weight('bold')
                    ->color('primary')
                    ->formatStateUsing(fn(string $state, JadwalKuliah $record): string => "Kelas {$state}")
                    ->description(
                        fn(JadwalKuliah $record): string => ($record->kelas->prodi->kode_prodi_internal ?? 'UMUM') .
                            ' (Angkatan ' . ($record->kelas->angkatan->id_tahun ?? '-') . ')'
                    ),

                // 3. JADWAL (HARI & JAM DIGABUNG AGAR LEBIH RAPI)
                TextColumn::make('hari')
                    ->label('Waktu Kuliah')
                    ->sortable()
                    ->badge()
                    ->icon('heroicon-o-clock')
                    ->color(fn(string $state): string => match ($state) {
                        'Sabtu', 'Minggu' => 'warning',
                        default => 'success',
                    })
                    ->formatStateUsing(function (JadwalKuliah $record): string {
                        $mulai = $record->jam_mulai ? date('H:i', strtotime($record->jam_mulai)) : '--:--';
                        $selesai = $record->jam_selesai ? date('H:i', strtotime($record->jam_selesai)) : '--:--';
                        return "{$record->hari}, {$mulai} - {$selesai}";
                    }),

                // 4. RUANGAN
                TextColumn::make('ruang.nama_ruang')
                    ->label('Ruangan')
                    ->sortable()
                    ->searchable()
                    ->weight('medium')
                    ->icon('heroicon-o-map-pin')
                    ->iconColor('gray')
                    ->description(fn(JadwalKuliah $record) => $record->ruang->jenis_ruang ?? 'TEORI'),

                // 5. DOSEN PENGAJAR
                TextColumn::make('dosenPengajars.dosen.person.nama_lengkap')
                    ->label('Dosen Pengajar')
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->searchable()
                    ->placeholder('Belum ditugaskan')
                    ->color('gray'),

                // 6. KAPASITAS KELAS
                TextColumn::make('isi_kelas')
                    ->label('Kapasitas')
                    ->alignCenter()
                    ->formatStateUsing(fn(int $state, JadwalKuliah $record): string => "{$state} / {$record->kuota_kelas}")
                    ->badge()
                    ->color(function (int $state, JadwalKuliah $record): string {
                        if ($record->kuota_kelas <= 0) return 'gray';
                        $persen = $state / $record->kuota_kelas * 100;

                        return match (true) {
                            $state >= $record->kuota_kelas => 'danger',
                            $persen >= 80 => 'warning',
                            default => 'info',
                        };
                    })
                    ->tooltip('Terisi / Kuota Maksimal'),

                // 7. STATUS KUNCI
                IconColumn::make('is_locked')
                    ->label('Terkunci')
                    ->boolean()
                    ->trueIcon('heroicon-s-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('danger')
                    ->falseColor('gray')
                    ->alignCenter()
                    ->tooltip('Jika dikunci (merah), jadwal ini aman dari timpaan sistem/generate otomatis.'),
            ])
            ->filters([
                TrashedFilter::make()->native(false),

                SelectFilter::make('tahun_akademik_id')
                    ->label('Tahun Akademik')
                    ->relationship('tahunAkademik', 'nama_tahun')
                    ->searchable()
                    ->preload()
                    ->default(fn() => RefTahunAkademik::where('is_active', true)->first()?->id),

                SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->options(function () {
                        return \App\Models\Kelas::query()
                            ->with(['prodi', 'angkatan'])
                            ->orderByDesc('angkatan_id')
                            ->orderBy('nama_kelas')
                            ->get()
                            ->mapWithKeys(fn($kelas) => [
                                $kelas->id => sprintf(
                                    'Kelas %s - %s (%s)',
                                    $kelas->nama_kelas,
                                    $kelas->prodi->kode_prodi_internal ?? 'UMUM',
                                    $kelas->angkatan->id_tahun ?? '-'
                                ),
                            ]);
                    })
                    ->searchable()
                    ->preload()
                    ->native(false),

                SelectFilter::make('dosen_id')
                    ->label('Dosen Pengajar')
                    ->options(function () {
                        return TrxDosen::query()
                            ->with('person')
                            ->get()
                            ->mapWithKeys(fn($dosen) => [
                                $dosen->id => $dosen->person->nama_lengkap ?? 'Tanpa Nama',
                            ])
                            ->sortBy(fn($nama) => $nama)
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] ?? null,
                            fn(Builder $query, $dosenId) => $query->whereHas(
                                'dosenPengajars',
                                fn(Builder $q) => $q->where('dosen_id', $dosenId)
                            )
                        );
                    }),

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
            ->filtersFormColumns(2)
            ->recordActions([
                Action::make('toggleLock')
                    ->label(fn($record) => $record->is_locked ? 'Buka Kunci' : 'Kunci Jadwal')
                    ->icon(fn($record) => $record->is_locked ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
                    ->color(fn($record) => $record->is_locked ? 'gray' : 'danger')
                    ->action(function ($record) {
                        $record->update(['is_locked' => !$record->is_locked]);
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Jadwal Kuliah')
            ->emptyStateDescription('Buat jadwal kuliah baru atau generate otomatis melalui menu yang tersedia.')
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->striped();
    }
}
