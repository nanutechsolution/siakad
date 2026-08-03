<?php

declare(strict_types=1);

namespace App\Filament\Dosen\Pages;

use App\Models\JadwalKuliah;
use App\Models\KrsDetail;
use App\Services\Dosen\GradeService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class InputNilaiKelas extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $title = 'Input Nilai Kelas';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.dosen.pages.input-nilai-kelas';

    protected static ?string $slug = 'input-nilai-kelas/{record}';

    public ?JadwalKuliah $record = null;

    public bool $isInputOpen = false;

    /**
     * Cache komponen nilai kelas ini supaya tidak query berulang.
     *
     * @var Collection<int, \App\Models\KurikulumKomponenNilai>
     */
    public Collection $komponenAktif;

    public function mount($record = null): void
    {
        $parameter = $record ?? request()->route('record');

        if ($parameter instanceof JadwalKuliah) {
            $jadwal = $parameter;
        } else {
            $jadwal = JadwalKuliah::find($parameter);
        }

        if (! $jadwal) {
            abort(404, "HALAMAN GAGAL DIMUAT: Parameter yang dibaca adalah [" . json_encode($parameter) . "]");
        }

        $jadwal->loadMissing(['tahunAkademik', 'mataKuliah', 'kelas']);
        Gate::authorize('nilaiKelasDosen', $jadwal);

        $this->record = $jadwal;
        $this->isInputOpen = $jadwal->tahunAkademik?->isInputNilaiOpen() ?? false;
        $this->komponenAktif = \App\Models\KurikulumKomponenNilai::with('komponen')
            ->where('kurikulum_id', $jadwal->kurikulum_id)
            ->get();
    }

    public function table(Table $table): Table
    {
        if ($this->record) {
            Gate::authorize('nilaiKelasDosen', $this->record);
        }

        $columns = [
            TextColumn::make('krs.mahasiswa.nim')
                ->label('NIM')
                ->searchable()
                ->sortable(),
            TextColumn::make('krs.mahasiswa.person.nama_lengkap')
                ->label('Nama Mahasiswa')
                ->searchable()
                ->sortable(),
        ];

        foreach ($this->komponenAktif as $komponen) {
            $columns[] = TextInputColumn::make('komp_' . $komponen->komponen_id)
                ->label($komponen->komponen->nama_komponen . ' (' . $komponen->bobot_persen . '%)')
                ->rules(['numeric', 'min:0', 'max:100'])
                ->disabled(! $this->isInputOpen) // Otomatis disabled jika periode input ditutup
                ->getStateUsing(fn(KrsDetail $record) => $record->getNilaiKomponen((int) $komponen->komponen_id))
                ->updateStateUsing(function (KrsDetail $record, $state) use ($komponen) {
                    if (! Gate::allows('inputNilaiDosen', $record)) {
                        Notification::make()
                            ->danger()
                            ->title('Gagal Menyimpan')
                            ->body('Anda tidak memiliki akses untuk mengubah nilai mahasiswa ini atau kelas sudah dikunci.')
                            ->send();
                        return;
                    }

                    \App\Models\KrsDetailNilai::updateOrCreate(
                        ['krs_detail_id' => $record->id, 'komponen_id' => $komponen->komponen_id],
                        ['nilai_angka' => (float) $state]
                    );

                    // FEEDBACK UI/UX: Beri konfirmasi langsung saat nilai komponen disimpan
                    Notification::make()
                        ->success()
                        ->title('Nilai Tersimpan')
                        ->body("Nilai {$komponen->komponen->nama_komponen} berhasil diperbarui.")
                        ->duration(2500)
                        ->send();
                });
        }

        $columns[] = TextColumn::make('nilai_angka')
            ->label('Nilai Akhir')
            ->numeric(2)
            ->placeholder('-')
            ->tooltip('Klik tombol "Hitung Nilai Akhir" di atas jika angka belum ter-update')
            ->sortable();

        $columns[] = TextColumn::make('nilai_huruf')
            ->label('Grade')
            ->badge() // Visual yang lebih jelas berupa badge
            ->color(fn($state) => match ($state) {
                'A', 'A-' => 'success',
                'B+', 'B', 'B-' => 'info',
                'C+', 'C' => 'warning',
                'D', 'E' => 'danger',
                default => 'gray',
            })
            ->placeholder('-')
            ->alignCenter();

        $columns[] = IconColumn::make('is_published')
            ->label('Status Kunci')
            ->boolean()
            ->trueIcon('heroicon-o-lock-closed')
            ->falseIcon('heroicon-o-lock-open')
            ->trueColor('danger')
            ->falseColor('success')
            ->tooltip(fn($state) => $state ? 'Nilai sudah dikunci (published)' : 'Nilai masih dapat diubah');

        return $table
            ->query(
                KrsDetail::query()
                    ->with(['krs.mahasiswa.person', 'detailNilai', 'jadwalKuliah'])
                    ->where('jadwal_kuliah_id', $this->record->id)
                    ->where('status_ambil', '!=', 'K')
            )
            ->columns($columns)
            ->recordActions([
                Action::make('revisi_nilai')
                    ->label('Revisi Nilai')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->visible(function (KrsDetail $record) {
                        // 1. Cek otorisasi dasar dari Gate
                        $canRevisi = Gate::allows('revisiNilaiDosen', $record);

                        // 2. Revisi HANYA logis jika nilai sudah dikunci/publish.
                        // Jika belum dikunci, dosen harusnya menginput/mengubah nilai secara normal, bukan via revisi.
                        $isPublished = $record->is_published === true;

                        // 3. Gunakan nilai_huruf (A, B, C, dll) sebagai penanda otentik bahwa nilai sudah pernah dikalkulasi.
                        // Jika masih null, berarti belum dihitung oleh GradeService.
                        $hasGrade = filled($record->nilai_huruf);

                        return $canRevisi && $isPublished && $hasGrade;
                    })
                    ->modalHeading('Pengajuan Revisi Nilai Mahasiswa')
                    ->modalDescription('Nilai kelas ini telah dikunci. Pengisian form ini akan mengajukan perubahan nilai resmi ke Akademik.')
                    ->schema([
                        TextInput::make('new_nilai_angka')
                            ->label('Nilai Angka Baru (0 - 100)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(100),
                        TextInput::make('nomor_sk_perbaikan')
                            ->label('Nomor SK / Surat Perbaikan (Opsional)')
                            ->placeholder('Contoh: SK/001/FT/2026')
                            ->maxLength(255),
                        Textarea::make('alasan_perbaikan')
                            ->label('Alasan Revisi Nilai')
                            ->placeholder('Tuliskan alasan perubahan nilai (misal: susulan tugas / koreksi Ujian)...')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (KrsDetail $record, array $data, GradeService $service) {
                        if (! filled($record->nilai_angka)) {
                            Notification::make()
                                ->danger()
                                ->title('Gagal Revisi Nilai')
                                ->body('Mahasiswa ini belum memiliki nilai awal. Silakan hitung nilai akhir terlebih dahulu.')
                                ->send();

                            return;
                        }

                        if (! Gate::allows('revisiNilaiDosen', $record)) {
                            abort(403);
                        }
                        $service->applyRevision(
                            krsDetail: $record,
                            nilaiAngkaBaru: (float) $data['new_nilai_angka'],
                            alasanPerbaikan: $data['alasan_perbaikan'],
                            nomorSkPerbaikan: $data['nomor_sk_perbaikan'] ?? null,
                            executedByUserId: (string) Auth::id(),
                        );

                        Notification::make()
                            ->success()
                            ->title('Pengajuan Revisi Berhasil')
                            ->body('Revisi nilai telah disimpan dan diperbarui.')
                            ->send();
                    }),
            ])
            ->headerActions([
                Action::make('hitung_ulang')
                    ->label('1. Hitung Nilai Akhir')
                    ->icon('heroicon-o-calculator')
                    ->color('primary')
                    ->tooltip('Kalkulasi ulang Nilai Akhir dan Huruf (Grade) berdasarkan bobot komponen')
                    ->disabled(fn() => ! $this->isInputOpen)
                    ->action(function (GradeService $service) {
                        $service->calculateFinalGradesForClass($this->record);
                        Notification::make()
                            ->success()
                            ->title('Kalkulasi Selesai')
                            ->body('Seluruh Nilai Akhir dan Grade mahasiswa telah dihitung ulang berdasarkan bobot komponen.')
                            ->send();
                    }),

                Action::make('publish_nilai')
                    ->label('2. Submit & Publish Kelas')
                    ->icon('heroicon-o-check-badge')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Kunci & Publish Nilai Kelas?')
                    ->modalDescription('PERHATIAN: Aksi ini akan mengunci seluruh nilai mahasiswa pada mata kuliah ini. Dosen tidak dapat mengubah nilai komponen secara langsung lagi setelah dipublish.')
                    ->modalSubmitActionLabel('Ya, Publish & Kunci Nilai')
                    ->disabled(fn() => ! $this->isInputOpen)
                    ->visible(fn() => Gate::allows('publishNilaiDosen', $this->record))
                    ->action(function (GradeService $service) {
                        $count = $service->publishClassGrades($this->record);
                        Notification::make()
                            ->success()
                            ->title('Kelas Berhasil Dipublish')
                            ->body("Berhasil mengunci dan mempublikasikan nilai untuk {$count} mahasiswa.")
                            ->send();
                    }),

                ActionGroup::make([
                    Action::make('print_pdf')
                        ->label('Cetak Presensi / Nilai (PDF)')
                        ->icon('heroicon-o-printer')
                        ->action(function () {
                            $this->js("window.open('" . route('dosen.nilai.print', ['id' => $this->record->id]) . "', '_blank')");
                        }),

                    Action::make('export_nilai')
                        ->label('Export Data Nilai (CSV)')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function () {
                            $jadwal = $this->record;
                            $filename = "Nilai_" . str_replace(' ', '_', $jadwal->mataKuliah?->nama_mk ?? 'MK') . "_" . ($jadwal->kelas?->nama_kelas ?? 'Kelas') . ".csv";

                            return response()->streamDownload(function () use ($jadwal) {
                                $output = fopen('php://output', 'w');
                                $header = ['NIM', 'Nama Mahasiswa'];
                                foreach ($this->komponenAktif as $komponen) {
                                    $header[] = $komponen->komponen->nama_komponen . ' (' . $komponen->bobot_persen . '%)';
                                }
                                $header[] = 'Angka Akhir';
                                $header[] = 'Huruf';
                                fputcsv($output, $header);

                                $peserta = KrsDetail::query()
                                    ->with(['krs.mahasiswa.person', 'detailNilai', 'jadwalKuliah'])
                                    ->where('jadwal_kuliah_id', $jadwal->id)
                                    ->where('status_ambil', '!=', 'K')
                                    ->get();

                                foreach ($peserta as $row) {
                                    $dataRow = [$row->krs?->mahasiswa?->nim ?? '', $row->krs?->mahasiswa?->person?->nama_lengkap ?? ''];
                                    foreach ($this->komponenAktif as $komponen) {
                                        $dataRow[] = $row->getNilaiKomponen((int) $komponen->komponen_id);
                                    }
                                    $dataRow[] = $row->nilai_angka;
                                    $dataRow[] = $row->nilai_huruf;
                                    fputcsv($output, $dataRow);
                                }
                                fclose($output);
                            }, $filename, [
                                'Content-Type' => 'text/csv',
                                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                            ]);
                        }),
                ])
                    ->label('Opsi Lainnya')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->button(),
            ]);
    }
}
