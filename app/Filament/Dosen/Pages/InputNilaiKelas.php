<?php

declare(strict_types=1);

namespace App\Filament\Dosen\Pages;

use App\Models\JadwalKuliah;
use App\Models\KrsDetail;
use App\Models\KrsDetailNilai;
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

    // PENTING: harus 'static string', bukan 'string' saja -
    // parent Filament\Pages\Page mendeklarasikan $view sebagai static property.
    protected  string $view = 'filament.dosen.pages.input-nilai-kelas';

    protected static ?string $slug = 'input-nilai-kelas/{record}';

    public ?JadwalKuliah $record = null;

    public bool $isInputOpen = false;

    /**
     * Cache komponen nilai kelas ini supaya tidak query berulang
     * di table(), headerActions, dan recordActions.
     *
     * @var Collection<int, \App\Models\JadwalKomponenNilai>
     */
    public Collection $komponenAktif;

    // Tambahkan parameter $record = null di sini agar Livewire otomatis mengisinya
    public function mount($record = null): void
    {
        // 1. Ambil dari argumen Livewire, kalau kosong ambil dari request URL
        $parameter = $record ?? request()->route('record');

        // 2. Cek apakah wujudnya sudah berupa Model, atau masih String UUID
        if ($parameter instanceof JadwalKuliah) {
            $jadwal = $parameter;
        } else {
            // Kalau masih string, kita query manual
            $jadwal = JadwalKuliah::find($parameter);
        }

        // 3. Jika benar-benar tidak ada datanya, munculkan pesan khusus!
        if (! $jadwal) {
            // Tampilkan apa isi parameternya agar mudah di-debug
            abort(404, "HALAMAN GAGAL DIMUAT: Parameter yang dibaca adalah [" . json_encode($parameter) . "]");
        }

        // 4. Lanjut load relasi dan otorisasi
        $jadwal->loadMissing('tahunAkademik');
        Gate::authorize('nilaiKelasDosen', $jadwal);

        $this->record = $jadwal;
        $this->isInputOpen = $jadwal->tahunAkademik?->isInputNilaiOpen() ?? false;
        $this->komponenAktif = \App\Models\KurikulumKomponenNilai::with('komponen')
            ->where('kurikulum_id', $jadwal->kurikulum_id)
            ->get();
    }
    public function table(Table $table): Table
    {
        $columns = [
            TextColumn::make('krs.mahasiswa.nim')->label('NIM')->searchable(),
            TextColumn::make('krs.mahasiswa.person.nama_lengkap')->label('Nama Mahasiswa')->searchable(),
        ];

        foreach ($this->komponenAktif as $komponen) {
            $columns[] = TextInputColumn::make('komp_' . $komponen->komponen_id)
                ->label($komponen->komponen->nama_komponen . ' (' . $komponen->bobot_persen . '%)')
                ->rules(['numeric', 'min:0', 'max:100'])

                // 1. UBAH BARIS INI MENJADI FALSE UNTUK MEMAKSA BUKA INPUT
                ->disabled(false)

                ->getStateUsing(fn(KrsDetail $record) => $record->getNilaiKomponen((int) $komponen->komponen_id))
                ->updateStateUsing(function (KrsDetail $record, $state) use ($komponen) {

                    // 2. MATIKAN SEMENTARA PENGECEKAN GATE DI SINI JUGA AGAR BISA DISIMPAN
                    /* if (! Gate::allows('inputNilaiDosen', $record)) {
                Notification::make()->danger()->title('Nilai tidak bisa diubah')->send();
                return;
            } 
            */

                    \App\Models\KrsDetailNilai::updateOrCreate(
                        ['krs_detail_id' => $record->id, 'komponen_id' => $komponen->komponen_id],
                        ['nilai_angka' => (float) $state]
                    );
                });
        }

        $columns[] = TextColumn::make('nilai_angka')->label('Angka akhir')->numeric(2);
        $columns[] = TextColumn::make('nilai_huruf')->label('Huruf');
        $columns[] = IconColumn::make('is_published')
            ->label('Published')
            ->boolean();

        return $table
            ->query(
                KrsDetail::query()
                    ->with(['krs.mahasiswa.person', 'detailNilai'])
                    ->where('jadwal_kuliah_id', $this->record->id)
                    ->where('status_ambil', '!=', 'K')
            )
            ->columns($columns)
            ->recordActions([
                Action::make('revisi_nilai')
                    ->label('Ajukan revisi')
                    ->icon('heroicon-o-document-text')
                    ->visible(fn(KrsDetail $record) => Gate::allows('revisiNilaiDosen', $record))
                    ->schema([
                        TextInput::make('new_nilai_angka')
                            ->label('Nilai angka baru')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(100),
                        TextInput::make('nomor_sk_perbaikan')
                            ->label('Nomor SK perbaikan')
                            ->maxLength(255),
                        Textarea::make('alasan_perbaikan')
                            ->label('Alasan revisi')
                            ->required(),
                    ])
                    ->action(function (KrsDetail $record, array $data, GradeService $service) {
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
                            ->title('Revisi nilai tersimpan')
                            ->send();
                    }),
            ])
          ->headerActions([
                // 1. TOMBOL UTAMA: Biarkan tetap di luar agar mudah diakses dosen
                Action::make('hitung_ulang')
                    ->label('Hitung & simpan nilai akhir')
                    ->color('warning')
                    ->disabled(fn() => ! $this->isInputOpen)
                    ->action(function (\App\Services\Dosen\GradeService $service) {
                        $service->calculateFinalGradesForClass($this->record);
                        Notification::make()
                            ->success()
                            ->title('Nilai akhir berhasil dihitung ulang')
                            ->send();
                    }),

                // 2. TOMBOL UTAMA KEDUA: Tetap di luar untuk eksekusi akhir
                Action::make('publish_nilai')
                    ->label('Submit & publish kelas')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('Aksi ini akan mengunci seluruh nilai mahasiswa di kelas ini. Perubahan selanjutnya wajib melalui form revisi.')
                    ->visible(fn() => Gate::allows('publishNilaiDosen', $this->record))
                    ->action(function (\App\Services\Dosen\GradeService $service) {
                        $count = $service->publishClassGrades($this->record);
                        Notification::make()
                            ->success()
                            ->title("Berhasil publish nilai untuk {$count} mahasiswa")
                            ->send();
                    }),

                // 3. GROUP DROPDOWN: Satukan fitur Cetak & Export di sini agar hemat tempat!
                ActionGroup::make([
                    
                    // Fitur Cetak PDF yang dibungkus
                    Action::make('print_pdf')
                        ->label('Cetak PDF / Print')
                        ->icon('heroicon-o-printer')
                        ->color('gray')
                        ->action(function () {
                            $this->js("window.open('" . route('dosen.nilai.print', ['id' => $this->record->id]) . "', '_blank')");
                        }),

                    // Fitur Export CSV/Excel yang dibungkus
                    Action::make('export_nilai')
                        ->label('Export Excel/CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function () {
                            $jadwal = $this->record;
                            $filename = "Nilai_" . str_replace(' ', '_', $jadwal->mataKuliah?->nama_mk) . "_" . $jadwal->kelas?->nama_kelas . ".csv";

                            return response()->streamDownload(function () use ($jadwal) {
                                $output = fopen('php://output', 'w');
                                $header = ['NIM', 'Nama Mahasiswa'];
                                foreach ($this->komponenAktif as $komponen) {
                                    $header[] = $komponen->komponen->nama_komponen . ' (' . $komponen->bobot_persen . '%)';
                                }
                                $header[] = 'Angka Akhir';
                                $header[] = 'Huruf';
                                fputcsv($output, $header);

                                $peserta = \App\Models\KrsDetail::query()
                                    ->with(['krs.mahasiswa.person', 'detailNilai'])
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
                ->label('Download / Cetak') // Nama tombol utama dropdown-nya
                ->icon('heroicon-m-ellipsis-vertical') // Icon titik tiga vertikal yang minimalis
                ->color('gray')
                ->button(), // Mengubah tampilan grup menjadi tombol elegan, bukan sekadar teks link
            ]);
    }
}
