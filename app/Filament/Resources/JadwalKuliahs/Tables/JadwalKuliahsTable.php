<?php

namespace App\Filament\Resources\JadwalKuliahs\Tables;

use App\Models\JadwalKuliah;
use App\Models\RefTahunAkademik;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
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
            // Urutkan berdasarkan Hari (Senin -> Minggu) lalu Jam Mulai, bukan created_at.
            // Jauh lebih bermakna untuk tabel jadwal kuliah.
            ->modifyQueryUsing(function (Builder $query) {
                return $query->orderByRaw(
                    "FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')"
                )->orderBy('jam_mulai');
            })
            ->columns([
                TextColumn::make('mataKuliah.kode_mk')
                    ->label('Kode MK')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('mataKuliah.nama_mk')
                    ->label('Mata Kuliah')
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->weight('bold')
                    ->description(fn(JadwalKuliah $record): ?string => $record->mataKuliah?->sks_default
                        ? $record->mataKuliah->sks_default . ' SKS'
                        : null),
                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('hari')
                    ->label('Jadwal')
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Sabtu', 'Minggu' => 'warning',
                        default => 'primary',
                    })
                    ->description(
                        fn(JadwalKuliah $record): string => ($record->jam_mulai ? date('H:i', strtotime($record->jam_mulai)) : '--:--') . ' - ' .
                            ($record->jam_selesai ? date('H:i', strtotime($record->jam_selesai)) : '--:--')
                    ),

                TextColumn::make('ruang.nama_ruang')
                    ->label('Ruangan')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-map-pin')
                    ->toggleable(),

                TextColumn::make('dosenPengajars.dosen.person.nama_lengkap')
                    ->label('Dosen Pengajar')
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->searchable()
                    ->placeholder('Belum ada dosen ditugaskan'),

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
                    }),

                TextColumn::make('tahunAkademik.nama_tahun')
                    ->label('Tahun Akademik')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->toggleable(),

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
                TrashedFilter::make()
                    ->native(false),

                SelectFilter::make('tahun_akademik_id')
                    ->label('Tahun Akademik')
                    ->relationship('tahunAkademik', 'nama_tahun')
                    ->searchable()
                    ->preload()
                    // Default: langsung terfilter ke Tahun Akademik yang sedang aktif
                    ->default(fn() => RefTahunAkademik::where('is_active', true)->first()?->id),

                SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->relationship('kelas', 'nama_kelas')
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
            ->headerActions([
                Action::make('exportPdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
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
