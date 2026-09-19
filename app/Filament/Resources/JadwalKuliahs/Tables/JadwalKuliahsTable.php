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
use Filament\Actions\ViewAction as ActionsViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JadwalKuliahsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query
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
            ->groups([
                Group::make('hari')
                    ->label('Jadwal Hari')
                    ->collapsible()
                    ->titlePrefixedWithLabel(false), // Menghilangkan tulisan kaku "Jadwal Hari:" di header
            ])
            ->defaultGroup('hari') // Otomatis aktif saat halaman dibuka
            ->columns([
                // 1. INFO MATA KULIAH & KELAS (Digabung agar layar tidak penuh)
                TextColumn::make('mataKuliah.nama_mk')
                    ->label('Informasi Perkuliahan')
                    ->sortable()
                    ->searchable(['nama_mk', 'kode_mk'])
                    ->wrap()
                    ->weight('bold')
                    ->color('primary')
                    ->formatStateUsing(fn(string $state, JadwalKuliah $record): string => "{$state} ({$record->mataKuliah->sks_default} SKS)")
                    ->description(
                        fn(JadwalKuliah $record): string =>
                        'Kelas ' . ($record->kelas->nama_kelas ?? '-') .
                            ' • Prodi ' . ($record->kelas->prodi->kode_prodi_internal ?? 'UMUM')
                    ),

                // 2. WAKTU KULIAH (Hari sudah ada di Group, jadi tampilkan Jam saja)
                TextColumn::make('jam_mulai')
                    ->label('Waktu')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-clock')
                    ->formatStateUsing(function (JadwalKuliah $record): string {
                        $mulai = $record->jam_mulai ? date('H:i', strtotime($record->jam_mulai)) : '--:--';
                        $selesai = $record->jam_selesai ? date('H:i', strtotime($record->jam_selesai)) : '--:--';
                        return "{$mulai} - {$selesai} WIB";
                    }),

                // 3. RUANG KELAS
                TextColumn::make('ruang.nama_ruang')
                    ->label('Ruangan')
                    ->sortable()
                    ->searchable()
                    ->weight('medium')
                    ->icon('heroicon-o-map-pin')
                    ->iconColor('gray')
                    ->description(fn(JadwalKuliah $record) => $record->ruang->jenis_ruang ?? 'TEORI'),

                // 4. DOSEN PENGAJAR (Bulleted list agar rapi dibaca awam)
                TextColumn::make('dosenPengajars.dosen.person.nama_lengkap')
                    ->label('Dosen Pengampu')
                    ->bulleted()
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->searchable()
                    ->placeholder('⚠️ Belum ada dosen')
                    ->color('gray'),

                // 5. KAPASITAS (Warna dinamis, bahasa manusiawi)
                TextColumn::make('isi_kelas')
                    ->label('Kapasitas')
                    ->alignCenter()
                    ->formatStateUsing(fn(int $state, JadwalKuliah $record): string => "{$state} / {$record->kuota_kelas} Kursi")
                    ->badge()
                    ->color(function (int $state, JadwalKuliah $record): string {
                        if ($record->kuota_kelas <= 0) return 'gray';
                        $persen = $state / $record->kuota_kelas * 100;

                        return match (true) {
                            $state >= $record->kuota_kelas => 'danger',
                            $persen >= 80 => 'warning',
                            default => 'info', // Biru muda untuk aman
                        };
                    }),

                // 6. STATUS KUNCI
                IconColumn::make('is_locked')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-s-shield-check') // Ganti gembok dengan shield (pelindung)
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('success') // Diubah: Kunci itu aman, jadi hijau. Tidak dikunci = rawan, jadi kuning/abu
                    ->falseColor('gray')
                    ->alignCenter()
                    ->tooltip('Hijau = Jadwal dikunci & aman. Abu-abu = Jadwal bisa tertimpa jika reset.'),
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
                    ->label('Kelas Mahasiswa')
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
                    ->label('Cari Jadwal Dosen')
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
            ])
            ->filtersFormColumns(1)
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->icon('heroicon-m-funnel')
                    ->slideOver()
            )
            ->recordActions([
                ActionsViewAction::make()
                    ->label('Detail')
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    ->slideOver(),

                Action::make('toggleLock')
                    ->label(fn($record) => $record->is_locked ? 'Buka Proteksi' : 'Proteksi Jadwal')
                    ->icon(fn($record) => $record->is_locked ? 'heroicon-m-lock-open' : 'heroicon-m-shield-check')
                    ->color(fn($record) => $record->is_locked ? 'warning' : 'success')
                    ->action(function ($record) {
                        $record->update(['is_locked' => !$record->is_locked]);
                    }),

                EditAction::make()
                    ->label('Ubah')
                    ->color('primary'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Jadwal Masih Kosong')
            ->emptyStateDescription('Tarik nafas dulu... lalu klik tombol "Buat" di atas untuk menyusun jadwal baru.')
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->striped();
    }
}
