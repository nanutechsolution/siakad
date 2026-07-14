<?php

namespace App\Filament\Resources\NilaiMonitorings\Pages;

use App\Enums\StatusNilaiKelas;
use App\Filament\Resources\NilaiMonitorings\NilaiMonitoringResource;
use App\Models\KrsDetail;
use App\Models\RefSkalaNilai;
use App\Services\NilaiBaraService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class DetailNilaiKelas extends Page implements HasTable
{
    use InteractsWithRecord, InteractsWithTable;
    protected static string $resource = NilaiMonitoringResource::class;

    protected string $view = 'filament.resources.nilai-monitorings.pages.detail-nilai-kelas';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
    public function getTitle(): string
    {
        return "Detail Nilai — {$this->record->mataKuliah?->nama_mk} ({$this->record->kelas?->nama_kelas})";
    }
    protected function getTableQuery(): Builder
    {
        return KrsDetail::query()
            ->where('jadwal_kuliah_id', $this->record->id)
            ->with(['mahasiswa.person', 'mataKuliah']);
    }
    public function table(Table $table): Table
    {
        return $table
            // Query mengarah ke KrsDetail milik Kelas (JadwalKuliah) ini
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('krs.mahasiswa.nim')->label('NIM')->searchable(),
                TextColumn::make('krs.mahasiswa.person.nama_lengkap')->label('Nama Mahasiswa')->searchable(),
                TextColumn::make('nilai_angka')->label('Nilai Angka'),
                TextColumn::make('nilai_huruf')->label('Huruf'),
                TextColumn::make('nilai_indeks')->label('Bobot'),
                IconColumn::make('is_published')
                    ->label('Publish')
                    ->boolean(),
                TextColumn::make('status')
                    ->label('Status')
                    ->state(fn(KrsDetail $record) => $record->statusNilai())
                    ->formatStateUsing(fn(StatusNilaiKelas $state) => $state->label())
                    ->badge()
                    ->color(fn(StatusNilaiKelas $state) => $state->color()),
                TextColumn::make('input_info')
                    ->label('Dosen Penginput / Waktu Input')
                    ->state(function (KrsDetail $record) {

                        $log = $this->getFirstActivity(
                            $record,
                            function ($a) {
                                $attrs = $a->attribute_changes?->get('attributes', []);
                                return is_array($attrs) && array_key_exists('nilai_huruf', $attrs);
                            }
                        );

                        if (! $log) {
                            return 'Belum ada data';
                        }

                        return ($log->causer?->name ?? '-')
                            . ' • '
                            . $log->created_at->format('d/m/Y H:i');
                    })
                    ->wrap(),

                TextColumn::make('publish_info')
                    ->label('Waktu Publish')
                    ->state(function (KrsDetail $record) {
                        $log = $this->getFirstActivity(
                            $record,
                            function ($a) {
                                $attrs = $a->attribute_changes?->get('attributes', []);
                                return is_array($attrs)
                                    && array_key_exists('is_published', $attrs)
                                    && filter_var($attrs['is_published'], FILTER_VALIDATE_BOOLEAN);
                            }
                        );

                        return $log ? $log->created_at->format('d/m/Y H:i') : '—';
                    }),
            ])
            ->recordActions([
                Action::make('riwayat')
                    ->label('Riwayat')
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->modalHeading('Riwayat Perubahan Nilai')
                    ->modalContent(fn(KrsDetail $record) => view(
                        'filament.resources.nilai-monitorings.pages.partials.riwayat-nilai',
                        ['logs' => $record->gradeRevisionLogs]
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
                Action::make('koreksi_nilai')
                    ->label('Koreksi Nilai')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->visible(fn() => auth()->user()?->can('edit_nilai'))
                    ->schema([
                        TextInput::make('nilai_angka_baru')
                            ->label('Nilai Angka Baru')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),

                        Select::make('nilai_huruf_baru')
                            ->label('Nilai Huruf Baru')
                            ->options(fn() => RefSkalaNilai::pluck('huruf', 'huruf'))
                            ->required(),

                        TextInput::make('nomor_sk_perbaikan')
                            ->label('Nomor SK Perbaikan (opsional)'),

                        Textarea::make('alasan_perbaikan')
                            ->label('Alasan Koreksi')
                            ->required()
                            ->rows(3)
                            ->helperText('Wajib diisi. Nilai lama tetap tersimpan pada riwayat.'),
                    ])
                    ->action(function (KrsDetail $record, array $data, NilaiBaraService $service) {
                        $service->koreksiNilai(
                            detail: $record,
                            nilaiAngkaBaru: (float) $data['nilai_angka_baru'],
                            nilaiHurufBaru: $data['nilai_huruf_baru'],
                            alasan: $data['alasan_perbaikan'],
                            nomorSk: $data['nomor_sk_perbaikan'] ?? null,
                        );

                        \Filament\Notifications\Notification::make()
                            ->title('Nilai berhasil dikoreksi')
                            ->success()
                            ->send();
                    }),

            ]);
    }

    /**
     * Ambil entri activity_log pertama yang memenuhi kondisi tertentu,
     * dipakai untuk menampilkan info "waktu input" & "waktu publish"
     * tanpa perlu kolom tambahan di krs_detail.
     */
    protected function getFirstActivity(KrsDetail $record, \Closure $condition): ?Activity
    {
        return Activity::query()
            ->where('subject_type', KrsDetail::class)
            ->where('subject_id', $record->id)
            ->where('log_name', 'nilai')
            ->orderBy('created_at')
            ->get()
            ->first($condition);
    }
}
