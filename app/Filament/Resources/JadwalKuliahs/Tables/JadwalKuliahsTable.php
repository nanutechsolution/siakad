<?php

namespace App\Filament\Resources\JadwalKuliahs\Tables;

use App\Models\JadwalKuliah;
use App\Models\RefTahunAkademik;
use App\Models\TrxDosen;
use Barryvdh\DomPDF\Facade\Pdf;
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
use Illuminate\Support\HtmlString;

class JadwalKuliahsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Urutkan berdasarkan Hari (Senin -> Minggu) lalu Jam Mulai
            ->modifyQueryUsing(function (Builder $query) {
                return $query->orderByRaw(
                    "FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')"
                )->orderBy('jam_mulai');
            })
            ->columns([
                // 1. MATA KULIAH (Digabung dengan Kode & SKS agar rapi)
                TextColumn::make('mataKuliah.nama_mk')
                    ->label('Mata Kuliah')
                    ->sortable()
                    ->searchable(['nama_mk', 'kode_mk']) // Bisa cari pakai kode juga
                    ->wrap()
                    ->weight('bold')
                    ->description(fn(JadwalKuliah $record) => new HtmlString(
                        '<span class="text-xs text-gray-500">' .
                            ($record->mataKuliah->kode_mk ?? '-') . ' &bull; <b>' .
                            ($record->mataKuliah->sks_default ?? 0) . ' SKS</b></span>'
                    )),

                // 2. KELAS, PRODI, & ANGKATAN
                // 2. KELAS, ANGKATAN, & PRODI
                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas & Angkatan')
                    ->sortable()
                    ->searchable()
                    ->weight('bold')
                    ->color('info')
                    ->formatStateUsing(
                        fn(string $state, JadwalKuliah $record): string =>
                        "Kelas {$state} (Angkatan " . ($record->kelas->angkatan->id_tahun ?? '-') . ")"
                    )
                    ->description(fn(JadwalKuliah $record) => new HtmlString(
                        '<span class="text-xs text-gray-500">' .
                            'Prodi: ' . ($record->kelas->prodi->kode_prodi_internal ?? 'Umum') .
                            '</span>'
                    )),
                // 3. JADWAL (Hari & Jam)
                TextColumn::make('hari')
                    ->label('Jadwal')
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Sabtu', 'Minggu' => 'warning',
                        default => 'success',
                    })
                    ->description(
                        fn(JadwalKuliah $record): string => ($record->jam_mulai ? date('H:i', strtotime($record->jam_mulai)) : '--:--') . ' - ' .
                            ($record->jam_selesai ? date('H:i', strtotime($record->jam_selesai)) : '--:--')
                    ),

                // 4. RUANGAN
                TextColumn::make('ruang.nama_ruang')
                    ->label('Ruangan')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-map-pin')
                    ->weight('medium')
                    ->description(fn(JadwalKuliah $record) => $record->ruang->jenis_ruang ?? 'TEORI'),

                // 5. DOSEN PENGAJAR
                TextColumn::make('dosenPengajars.dosen.person.nama_lengkap')
                    ->label('Dosen Pengajar')
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->searchable()
                    ->placeholder('Belum ada dosen ditugaskan'),

                // 6. KAPASITAS KELAS
                TextColumn::make('isi_kelas')
                    ->label('Kapasitas')
                    ->alignCenter()
                    ->formatStateUsing(fn(int $state, JadwalKuliah $record): string => $state . ' / ' . $record->kuota_kelas)
                    ->badge()
                    ->color(function (int $state, JadwalKuliah $record): string {
                        if ($record->kuota_kelas <= 0) return 'gray';
                        $persen = $state / $record->kuota_kelas * 100;

                        return match (true) {
                            $state >= $record->kuota_kelas => 'danger',
                            $persen >= 80 => 'warning',
                            default => 'success',
                        };
                    })
                    ->tooltip('Jumlah terisi / Kuota maksimal'),

                // 7. STATUS KUNCI (LOCK)
                IconColumn::make('is_locked')
                    ->label('Terkunci')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('danger')
                    ->falseColor('gray')
                    ->alignCenter()
                    ->tooltip('Jika merah, jadwal ini aman dari timpaan saat Generate ulang.'),

                // 8. TAHUN AKADEMIK (Bisa disembunyikan jika layar sempit)
                TextColumn::make('tahunAkademik.nama_tahun')
                    ->label('Tahun Akademik')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('kurikulum.nama_kurikulum')
                    ->label('Kurikulum')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                                    'Kelas %s-%s-%s',
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
                            fn(Builder $query, $dosenId) =>
                            $query->whereHas(
                                'dosenPengajars',
                                fn(Builder $q) =>
                                $q->where('dosen_id', $dosenId)
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
            ->headerActions([
                Action::make('exportPdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->action(function ($livewire) {
                        $jadwals = $livewire->getFilteredTableQuery()
                            ->with(['mataKuliah', 'kelas.prodi', 'ruang', 'dosenPengajars.dosen.person', 'tahunAkademik'])
                            ->get();
                        $activeTaLabel = RefTahunAkademik::where('is_active', true)->value('nama_tahun') ?? '-';
                        $pdf = Pdf::loadView('filament.resources.jadwal-kuliahs.exports.jadwal-pdf', [
                            'jadwals' => $jadwals,
                            'activeTaLabel' => $activeTaLabel,
                            'generatedAt' => now(),
                        ])->setPaper('a4', 'landscape');

                        return response()->streamDownload(
                            fn() => print($pdf->output()),
                            'jadwal-kuliah-' . now()->format('Y-m-d-His') . '.pdf'
                        );
                    }),
            ])
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
            ]);
    }
}
