<?php

namespace App\Filament\Mahasiswa\Pages;

use App\Enums\MahasiswaNavigationGroup;
use App\Filament\Mahasiswa\Concerns\ResolvesMahasiswa;
use App\Models\KrsDetail;
use App\Models\PerkuliahanAbsensi;
use App\Models\PerkuliahanSesi;
use App\Models\RefTahunAkademik;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class RiwayatPresensi extends Page implements HasTable
{
    use InteractsWithTable;
    use ResolvesMahasiswa;


    protected static string|UnitEnum|null $navigationGroup = MahasiswaNavigationGroup::PERKULIAHAN->value;
    protected static ?string $navigationLabel = 'Riwayat Presensi';
    protected static ?string $title = 'Riwayat Presensi';
    protected static ?int $navigationSort = 4;
    protected static ?string $slug = 'riwayat-presensi';
    protected string $view = 'filament.mahasiswa.pages.riwayat-presensi';
    /** Ambang batas persentase kehadiran minimum yang lazim disyaratkan untuk boleh ikut UTS/UAS. */
    protected const AMBANG_KEHADIRAN_MINIMUM = 75;

    public function mount(): void
    {
        $this->currentMahasiswa();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->heading('Rekap Kehadiran per Mata Kuliah')
            ->emptyStateHeading('Belum ada data presensi')
            ->emptyStateDescription(
                'Belum ada sesi perkuliahan yang tercatat untuk mata kuliah yang Anda ambil.'
            )
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->columns([
                TextColumn::make('mata_kuliah')
                    ->label('Mata Kuliah')
                    ->state(
                        fn(KrsDetail $record) =>
                        $record->nama_mk_snapshot
                            ?: $record->mataKuliah?->nama_mk
                            ?: '(Mata kuliah tidak dikenali)'
                    )
                    ->description(
                        fn(KrsDetail $record) =>
                        $record->jadwalKuliah?->tahunAkademik?->nama_tahun
                    )
                    ->wrap(),
                TextColumn::make('total_sesi_selesai')
                    ->label('Sesi Terlaksana')
                    ->alignCenter(),

                TextColumn::make('total_hadir')
                    ->label('Hadir')
                    ->badge()
                    ->color('success')
                    ->alignCenter(),

                TextColumn::make('total_izin')
                    ->label('Izin')
                    ->badge()
                    ->color('warning')
                    ->alignCenter(),

                TextColumn::make('total_sakit')
                    ->label('Sakit')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                TextColumn::make('total_alpa')
                    ->label('Alpa')
                    ->badge()
                    ->color('danger')
                    ->alignCenter(),

                TextColumn::make('persentase_kehadiran')
                    ->label('% Kehadiran')
                    ->state(fn(KrsDetail $record): string => $this->hitungPersentase($record) . '%')
                    ->badge()
                    ->color(
                        fn(KrsDetail $record): string =>
                        $this->hitungPersentase($record) >= self::AMBANG_KEHADIRAN_MINIMUM
                            ? 'success'
                            : 'danger'
                    ),
            ])
            ->filters([
                SelectFilter::make('tahun_akademik')
                    ->label('Semester')
                    ->options(fn(): array => $this->opsiTahunAkademik())
                    ->default(fn() => $this->tahunAkademikAktif()?->id)
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas(
                            'jadwalKuliah',
                            fn(Builder $q) => $q->where('tahun_akademik_id', $data['value'])
                        );
                    }),
            ])
            ->recordActions([
                Action::make('lihat_detail')
                    ->label('Detail per Pertemuan')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(
                        fn(KrsDetail $record): string =>
                        'Detail Presensi - ' . ($record->nama_mk_snapshot ?? $record->mataKuliah?->nama_mk)
                    )
                    ->modalContent(
                        fn(KrsDetail $record) => view(
                            'filament.mahasiswa.pages.partials.detail-presensi',
                            ['sesi' => $this->getDetailSesi($record)]
                        )
                    )
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
            ])
            ->defaultSort('nama_mk_snapshot')
            ->paginated([10, 25, 50]);
    }

    protected function getTableQuery(): Builder
    {
        return KrsDetail::query()
            ->whereHas(
                'krs',
                fn(Builder $q) => $q->where('mahasiswa_id', $this->currentMahasiswa()->id)->berlaku()
            )
            ->aktif()
            ->whereNotNull('jadwal_kuliah_id')
            ->with(['mataKuliah', 'jadwalKuliah.tahunAkademik'])
            ->withCount([
                'sesiKuliah as total_sesi_selesai' => fn(Builder $q) => $q->selesai(),
                'absensi as total_hadir' => fn(Builder $q) => $q->where('status_kehadiran', 'H'),
                'absensi as total_izin' => fn(Builder $q) => $q->where('status_kehadiran', 'I'),
                'absensi as total_sakit' => fn(Builder $q) => $q->where('status_kehadiran', 'S'),
                'absensi as total_alpa' => fn(Builder $q) => $q->where('status_kehadiran', 'A'),
            ]);
    }

    /**
     * Detail presensi per pertemuan untuk satu baris KRS, termasuk sesi
     * yang SUDAH terlaksana tapi belum ada baris presensi mahasiswa ini
     * sama sekali (ditandai eksplisit sebagai "Belum Tercatat", bukan
     * disembunyikan begitu saja).
     */
    protected function getDetailSesi(KrsDetail $krsDetail): \Illuminate\Support\Collection
    {
        $absensiPerSesi = PerkuliahanAbsensi::query()
            ->where('krs_detail_id', $krsDetail->id)
            ->get()
            ->keyBy('perkuliahan_sesi_id');

        return $krsDetail->sesiKuliah()
            ->selesai()
            ->orderBy('pertemuan_ke')
            ->get()
            ->map(function (PerkuliahanSesi $sesi) use ($absensiPerSesi) {
                $absensi = $absensiPerSesi->get($sesi->id);

                return (object) [
                    'pertemuan_ke' => $sesi->pertemuan_ke,
                    'tanggal' => $sesi->tanggal_efektif,
                    'status_label' => $absensi?->status_label ?? 'Belum Tercatat',
                    'status_kehadiran' => $absensi?->status_kehadiran,
                    'waktu_check_in' => $absensi?->waktu_check_in,
                    'is_manual_update' => $absensi?->is_manual_update ?? false,
                    'alasan_perubahan' => $absensi?->alasan_perubahan,
                ];
            });
    }

    protected function hitungPersentase(KrsDetail $record): int
    {
        $totalSesi = (int) ($record->total_sesi_selesai ?? 0);

        if ($totalSesi === 0) {
            return 0;
        }

        return (int) round((((int) $record->total_hadir) / $totalSesi) * 100);
    }

    /** @return array<int, string> */
    protected function opsiTahunAkademik(): array
    {
        return RefTahunAkademik::query()
            ->whereIn('id', $this->currentMahasiswa()->krs()->pluck('tahun_akademik_id'))
            ->orderByDesc('tanggal_mulai')
            ->pluck('nama_tahun', 'id')
            ->all();
    }
}
