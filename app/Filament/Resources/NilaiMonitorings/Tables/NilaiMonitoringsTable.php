<?php

namespace App\Filament\Resources\NilaiMonitorings\Tables;

use App\Enums\StatusNilaiKelas;
use App\Filament\Resources\NilaiMonitorings\Pages\DetailNilaiKelas;
use App\Models\JadwalKuliah;
use App\Models\RefFakultas;
use App\Models\RefProdi;
use App\Models\RefTahunAkademik;
use App\Models\TrxDosen;
use App\Services\NilaiBaraService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NilaiMonitoringsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tahunAkademik.nama_tahun')
                    ->label('Tahun Akademik')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tahunAkademik.semester')
                    ->label('Semester')
                    ->formatStateUsing(fn(int $state) => match ($state) {
                        1 => 'Ganjil',
                        2 => 'Genap',
                        3 => 'Pendek',
                        default => $state,
                    })
                    ->badge(),

                TextColumn::make('kelas.prodi.fakultas.nama_fakultas')
                    ->label('Fakultas')
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('kelas.prodi.nama_prodi')
                    ->label('Program Studi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('mataKuliah.nama_mk')
                    ->label('Mata Kuliah')
                    ->description(fn(JadwalKuliah $record) => $record->mataKuliah?->kode_mk)
                    ->searchable(),

                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas Kuliah')
                    ->sortable(),

                TextColumn::make('dosenPengampu.person.nama_lengkap')
                    ->label('Dosen Pengampu')
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->bulleted(),

                TextColumn::make('jumlah_mahasiswa')
                    ->label('Jml Mhs')
                    ->alignCenter()
                    ->badge(),

                TextColumn::make('status_nilai')
                    ->label('Status Nilai')
                    ->badge()
                    ->state(fn(JadwalKuliah $record) => $record->status_nilai)
                    ->formatStateUsing(fn(StatusNilaiKelas $state) => $state->label())
                    ->color(fn(StatusNilaiKelas $state) => $state->color())
                    ->icon(fn(StatusNilaiKelas $state) => $state->icon()),
            ])
            ->filters([
                SelectFilter::make('tahun_akademik_id')
                    ->label('Tahun Akademik')
                    ->options(fn() => RefTahunAkademik::orderByDesc('id')->pluck('nama_tahun', 'id'))
                    ->searchable(),

                SelectFilter::make('semester')
                    ->label('Semester')
                    ->options([1 => 'Ganjil', 2 => 'Genap', 3 => 'Pendek'])
                    ->query(fn(Builder $query, array $data) => $query->when(
                        $data['value'],
                        fn($q, $v) => $q->whereHas('tahunAkademik', fn($qq) => $qq->where('semester', $v))
                    )),

                SelectFilter::make('prodi')
                    ->label('Program Studi')
                    ->options(fn() => RefProdi::pluck('nama_prodi', 'id'))
                    ->query(fn(Builder $query, array $data) => $query->when(
                        $data['value'],
                        fn($q, $v) => $q->whereHas('kelas', fn($qq) => $qq->where('prodi_id', $v))
                    )),

                SelectFilter::make('fakultas')
                    ->label('Fakultas')
                    ->options(fn() => RefFakultas::pluck('nama_fakultas', 'id'))
                    ->query(fn(Builder $query, array $data) => $query->when(
                        $data['value'],
                        fn($q, $v) => $q->whereHas('kelas.prodi', fn($qq) => $qq->where('fakultas_id', $v))
                    )),

                SelectFilter::make('dosen')
                    ->label('Dosen Pengampu')
                    ->options(fn() => TrxDosen::with('person')->get()->pluck('person.nama_lengkap', 'id'))
                    ->searchable()
                    ->query(fn(Builder $query, array $data) => $query->when(
                        $data['value'],
                        fn($q, $v) => $q->whereHas('dosenPengampu', fn($qq) => $qq->where('trx_dosen.id', $v))
                    )),

                SelectFilter::make('status_nilai')
                    ->label('Status Nilai')
                    ->options(StatusNilaiKelas::options())
                    ->query(fn(Builder $query, array $data) => $query->when(
                        $data['value'],
                        fn($q, $v) => $q->statusNilai(StatusNilaiKelas::from($v))
                    )),
            ])
            ->recordActions([
                Action::make('lihat_detail')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn(JadwalKuliah $record) => DetailNilaiKelas::getUrl(['record' => $record]))
                    ->color('gray'),

                Action::make('lock_nilai')
                    ->label('Lock Nilai')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->visible(fn(JadwalKuliah $record) => auth()->user()?->can('lock_nilai')
                        && in_array($record->status_nilai, [StatusNilaiKelas::SUDAH_PUBLISH]))
                    ->requiresConfirmation()
                    ->schema([
                        Textarea::make('catatan')
                            ->label('Catatan (opsional)')
                            ->rows(2),
                    ])
                    ->action(function (JadwalKuliah $record, array $data, NilaiBaraService $service) {
                        $service->lockKelas($record, $data['catatan'] ?? null);
                        \Filament\Notifications\Notification::make()
                            ->title('Nilai berhasil dikunci')
                            ->success()
                            ->send();
                    }),

                Action::make('unlock_nilai')
                    ->label('Unlock Nilai')
                    ->icon('heroicon-o-lock-open')
                    ->color('warning')
                    ->visible(fn(JadwalKuliah $record) => auth()->user()?->can('unlock_nilai')
                        && $record->status_nilai === StatusNilaiKelas::TERKUNCI)
                    ->requiresConfirmation()
                    ->schema([
                        Textarea::make('alasan')
                            ->label('Alasan Unlock')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (JadwalKuliah $record, array $data, NilaiBaraService $service) {
                        $service->unlockKelas($record, $data['alasan']);

                        \Filament\Notifications\Notification::make()
                            ->title('Nilai berhasil dibuka kembali')
                            ->warning()
                            ->send();
                    }),
            ])->defaultSort('tahun_akademik_id', 'desc')
            ->striped()
            ->poll('60s');
    }
}
