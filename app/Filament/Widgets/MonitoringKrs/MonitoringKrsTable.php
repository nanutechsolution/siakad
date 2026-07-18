<?php

namespace App\Filament\Widgets\MonitoringKrs;

use App\Enums\KrsStatusEnum;
use App\Exports\MonitoringKrsExport;
use App\Models\Mahasiswa;
use App\Models\RefTahunAkademik;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Table READ-ONLY: sengaja tidak ada EditAction/DeleteAction/CreateAction,
 * sesuai requirement "bukan CRUD, murni monitoring".
 */
class MonitoringKrsTable extends BaseWidget
{
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Monitoring Mahasiswa')
            ->query($this->buildQuery())
            ->columns([
                TextColumn::make('nim')->label('NIM')->searchable()->sortable(),
                TextColumn::make('person.nama_lengkap')->label('Nama Mahasiswa')->searchable(),
                TextColumn::make('prodi.nama_prodi')->label('Program Studi')->sortable(),
                TextColumn::make('angkatan_id')->label('Angkatan')->sortable(),
                TextColumn::make('semester_berjalan')->label('Semester')->state(
                    fn(Mahasiswa $record) => $this->hitungSemester($record)
                ),
                TextColumn::make('krs_current.total_sks_diambil')->label('Jumlah SKS')->default('-'),
                TextColumn::make('krs_current.status_krs')
                    ->label('Status KRS')
                    ->badge()
                    ->formatStateUsing(fn(?string $state) => $state ? KrsStatusEnum::from($state)->getLabel() : 'Belum KRS')
                    ->color(fn(?string $state) => $state ? KrsStatusEnum::from($state)->getColor() : 'gray'),
                TextColumn::make('status_approval')->label('Status Approval')->state(function (Mahasiswa $record) {
                    $status = $record->krs_current?->status_krs;

                    return match ($status) {
                        'DIAJUKAN' => 'Menunggu Approval',
                        'DISETUJUI' => 'Disetujui',
                        'DITOLAK' => 'Ditolak',
                        default => '-',
                    };
                }),
                TextColumn::make('dosen_wali_nama')->label('Dosen Wali')->state(
                    fn(Mahasiswa $record) => $record->krs_current?->dosenWali?->person?->nama_lengkap
                        ?? $record->kelasAktif?->dosenWali?->person?->nama_lengkap
                        ?? '—'
                ),
                TextColumn::make('krs_current.updated_at')->label('Last Update')->dateTime('d M Y H:i')->default('-'),
            ])
            ->filters([
                // Filter tambahan spesifik tabel (di luar filter global halaman) bisa ditambah di sini
                // jika suatu saat dibutuhkan filter yang tidak relevan untuk chart/stats.
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Export Laporan')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->visible(fn() => auth()->user()->can('export_monitoring_krs'))
                    ->action(function () {
                        $taId = $this->pageFilters['tahun_akademik_id']
                            ?? RefTahunAkademik::query()->where('is_active', true)->value('id');

                        return Excel::download(
                            new MonitoringKrsExport($this->buildQuery(), (int) $taId),
                            'monitoring-krs-' . now()->format('Ymd-His') . '.xlsx'
                        );
                    }),
            ])
            ->paginated([25, 50, 100])
            ->deferLoading()
            ->poll('60s');
    }

    private function buildQuery(): Builder
    {
        $taId = $this->pageFilters['tahun_akademik_id']
            ?? RefTahunAkademik::query()->where('is_active', true)->value('id');

        return Mahasiswa::query()
            ->with(['person', 'prodi', 'krsCurrent.dosenWali.person', 'kelasAktif.dosenWali.person'])
            ->when($this->pageFilters['prodi_id'] ?? null, fn($q, $v) => $q->where('prodi_id', $v))
            ->when($this->pageFilters['fakultas_id'] ?? null, fn($q, $v) => $q
                ->whereHas('prodi', fn($qq) => $qq->where('fakultas_id', $v)))
            ->when($this->pageFilters['angkatan_id'] ?? null, fn($q, $v) => $q->where('angkatan_id', $v))
            ->when($taId, fn($q) => $q->whereHas('riwayatStatus', fn($qq) => $qq
                ->where('tahun_akademik_id', $taId)
                ->where('status_kuliah', 'A')))
            ->when($this->pageFilters['status_krs'] ?? null, function ($q, $status) use ($taId) {
                if ($status === 'BELUM_KRS') {
                    return $q->whereDoesntHave('krs', fn($qq) => $qq
                        ->where('tahun_akademik_id', $taId)
                        ->whereIn('status_krs', ['DIAJUKAN', 'DISETUJUI', 'DITOLAK']));
                }

                return $q->whereHas('krs', fn($qq) => $qq
                    ->where('tahun_akademik_id', $taId)
                    ->where('status_krs', $status));
            });
    }

    private function hitungSemester(Mahasiswa $record): int
    {
        $taAktifKode = (int) (RefTahunAkademik::query()->where('is_active', true)->value('kode_tahun') ?? 0);
        $angkatan = (int) $record->angkatan_id;

        // Asumsi kode_tahun berformat 2 digit tahun + semester (mis. '241' = 2024 Ganjil).
        // Sesuaikan rumus ini dengan konvensi kode_tahun aktual di instansi Anda.
        return max((($taAktifKode - $angkatan) ?: 1), 1);
    }
}
