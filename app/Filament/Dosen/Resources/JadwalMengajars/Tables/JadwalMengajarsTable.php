<?php

namespace App\Filament\Dosen\Resources\JadwalMengajars\Tables;

use App\Filament\Dosen\Resources\JadwalMengajars\JadwalMengajarResource;
use App\Models\JadwalKuliah;
use App\Models\RefTahunAkademik;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;
use Illuminate\Database\Eloquent\Builder;

class JadwalMengajarsTable
{
    private const DAY_COLORS = [
        'Senin' => 'info',
        'Selasa' => 'success',
        'Rabu' => 'warning',
        'Kamis' => 'primary',
        'Jumat' => 'danger',
        'Sabtu' => 'gray',
        'Minggu' => 'gray',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query
                ->with([
                    'mataKuliah',
                    'kelas.prodi',
                    'kelas.angkatan',
                    'ruang',
                    'tahunAkademik',
                ])
                ->orderByRaw("FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")
                ->orderBy('jam_mulai'))
            ->groups([
                Group::make('hari')
                    ->label('Hari Kuliah')
                    ->collapsible()
                    ->orderQueryUsing(fn(Builder $query, string $direction) => $query->orderByRaw(
                        "FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') {$direction}"
                    )),
            ])
            ->groupingSettingsHidden()
            ->defaultGroup('hari')
            ->columns([
                TextColumn::make('hari')
                    ->label('Hari')
                    ->badge()
                    ->color(fn(?string $state) => self::DAY_COLORS[$state ?? ''] ?? 'gray')
                    ->sortable(),

                TextColumn::make('jam_mulai')
                    ->label('Waktu Kuliah')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-clock')
                    ->formatStateUsing(function (JadwalKuliah $record): string {
                        $mulai = $record->jam_mulai ? Carbon::parse($record->jam_mulai)->format('H:i') : '--:--';
                        $selesai = $record->jam_selesai ? Carbon::parse($record->jam_selesai)->format('H:i') : '--:--';

                        return "{$mulai} – {$selesai} WIB";
                    })
                    ->description(fn(JadwalKuliah $record): string => $record->jam_mulai
                        ? 'Jam masuk sampai jam selesai'
                        : 'Waktu belum diatur'),

                TextColumn::make('mataKuliah.nama_mk')
                    ->label('Mata Kuliah')
                    ->searchable(['nama_mk', 'kode_mk'])
                    ->sortable()
                    ->wrap()
                    ->weight('bold')
                    ->color('primary')
                    ->description(fn(JadwalKuliah $record): string => sprintf(
                        '%s · %s SKS · Kelas %s',
                        $record->mataKuliah?->kode_mk ?? 'Kode belum ada',
                        $record->mataKuliah?->sks_default ?? '-',
                        $record->kelas?->nama_kelas ?? 'Kelas belum diatur',
                    )),

                TextColumn::make('ruang.nama_ruang')
                    ->label('Ruangan')
                    ->placeholder('Belum ditentukan')
                    ->icon('heroicon-o-map-pin')
                    ->wrap(),

                TextColumn::make('peran')
                    ->label('Peran Anda')
                    ->state(fn(JadwalKuliah $record) => $record->dosenPengampu->first()?->is_koordinator
                        ? 'Koordinator'
                        : 'Anggota Tim')
                    ->badge()
                    ->color(fn(string $state) => $state === 'Koordinator' ? 'success' : 'gray'),

                TextColumn::make('progress')
                    ->label('Pertemuan')
                    ->state(function (JadwalKuliah $record): string {
                        $rencana = $record->dosenPengampu->first()?->rencana_tatap_muka ?? 14;

                        return "{$record->sesi_terlaksana_count} dari {$rencana} sesi";
                    })
                    ->badge()
                    ->color(function (JadwalKuliah $record): string {
                        $rencana = $record->dosenPengampu->first()?->rencana_tatap_muka ?? 14;

                        return $record->sesi_terlaksana_count >= $rencana ? 'success' : 'warning';
                    }),

                TextColumn::make('isi_kelas')
                    ->label('Peserta')
                    ->state(fn(JadwalKuliah $record): string => sprintf(
                        '%d dari %d peserta',
                        (int) $record->isi_kelas,
                        (int) $record->kuota_kelas,
                    ))
                    ->badge()
                    ->color(fn(JadwalKuliah $record): string => $record->kuota_kelas > 0 && $record->isi_kelas >= $record->kuota_kelas
                        ? 'danger'
                        : 'info'),

                TextColumn::make('tahunAkademik.nama_tahun')
                    ->label('Periode')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tahun_akademik_id')
                    ->label('Periode Kuliah')
                    ->relationship('tahunAkademik', 'nama_tahun')
                    ->default(fn() => RefTahunAkademik::where('is_active', true)->value('id')),

                SelectFilter::make('hari')
                    ->label('Hari')
                    ->options(array_combine(array_keys(JadwalKuliah::URUTAN_HARI), array_keys(JadwalKuliah::URUTAN_HARI))),
            ])
            ->striped()
            ->recordActions([
                Action::make('lihat')
                    ->label('Lihat Jadwal')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn(JadwalKuliah $record) => JadwalMengajarResource::getUrl('view', ['record' => $record])),
                Action::make('rekapKehadiran')
                    ->label('Rekap Kehadiran')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('primary')
                    ->url(fn(JadwalKuliah $record) => JadwalMengajarResource::getUrl('rekap-kehadiran', ['record' => $record])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ])
            ->emptyStateHeading('Belum ada jadwal mengajar')
            ->emptyStateDescription('Jadwal kuliah Anda pada periode yang dipilih akan muncul di sini.');
    }
}
