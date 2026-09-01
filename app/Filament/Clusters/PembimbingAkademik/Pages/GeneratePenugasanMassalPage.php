<?php

namespace App\Filament\Clusters\PembimbingAkademik\Pages;

use App\Enums\PembimbingAkademikJenis;
use App\Enums\PembimbingAkademikMode;
use App\Exceptions\PembimbingAkademikException;
use App\Filament\Clusters\PembimbingAkademik\PembimbingAkademikCluster;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\RefAngkatan;
use App\Models\RefProdi;
use App\Models\RefTahunAkademik;
use App\Models\TrxDosen;
use App\Services\PembimbingAkademikService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class GeneratePenugasanMassalPage extends Page implements HasForms
{
    use InteractsWithForms;
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bolt';
    protected static ?string $navigationLabel = 'Generate Massal';
    protected static ?string $title = 'Generate Penugasan Dosen Wali Massal';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.clusters.pembimbing-akademik.pages.generate-penugasan-massal-page';
    protected static ?string $cluster = PembimbingAkademikCluster::class;

    public ?array $data = [];
    public array $preview = [];

    public bool $previewGenerated = false;
    public int $processed = 0;
    public int $totalGagal = 0;

    public function mount(): void
    {
        $this->form->fill([
            'is_primary' => true,
            'tanggal_mulai' => now()->toDateString(),
            'semester_mulai_id' => RefTahunAkademik::query()->where('is_active', true)->value('id'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'lg' => 3])->schema([

                    // Langkah 1: Ruang Lingkup
                    Section::make('1. Ruang Lingkup Target')
                        ->description('Pilih prodi dan angkatan yang akan diproses.')
                        ->icon('heroicon-o-building-library')
                        ->columnSpan(['default' => 1, 'lg' => 1])
                        ->schema([
                            Select::make('prodi_id')
                                ->label('Program Studi')
                                ->options(fn() => RefProdi::query()->orderBy('nama_prodi')->pluck('nama_prodi', 'id'))
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(fn($livewire) => $livewire->resetGenerate())
                                ->required(),

                            Select::make('angkatan_id')
                                ->label('Angkatan')
                                ->options(fn() => RefAngkatan::query()->orderByDesc('id_tahun')->pluck('id_tahun', 'id_tahun'))
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(fn($livewire) => $livewire->resetGenerate())
                                ->required(),

                            Placeholder::make('info_konfigurasi')
                                ->label('')
                                ->visible(fn($get) => $get('prodi_id') && $get('angkatan_id'))
                                ->content(function ($get) {
                                    $service = app(PembimbingAkademikService::class);
                                    $konfigurasi = $service->konfigurasiAktif($get('prodi_id'), $get('angkatan_id'));

                                    if (! $konfigurasi) {
                                        return new HtmlString(
                                            '<div class="rounded-xl bg-danger-50 dark:bg-danger-500/10 p-4 text-sm text-danger-600 dark:text-danger-400 flex items-start gap-3 border border-danger-200 dark:border-danger-500/20">
                                                <svg class="h-5 w-5 shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" /></svg>
                                                <div><strong class="font-semibold">Belum ada konfigurasi aktif.</strong><br/>Generate massal tidak bisa dilanjutkan. Atur dulu di menu Konfigurasi Pembimbing.</div>
                                            </div>'
                                        );
                                    }

                                    $sisa = $konfigurasi->mode === PembimbingAkademikMode::PER_KELAS
                                        ? $service->kelasBelumPunyaWali((int) $get('prodi_id'), (int) $get('angkatan_id'))->count()
                                        : Mahasiswa::query()
                                        ->where('prodi_id', $get('prodi_id'))
                                        ->where('angkatan_id', $get('angkatan_id'))
                                        ->whereNull('deleted_at')
                                        ->whereDoesntHave('pembimbingAkademik', fn($q) => $q
                                            ->where('jenis', PembimbingAkademikJenis::DOSEN_WALI)
                                            ->where('status', 'AKTIF'))
                                        ->count();

                                    $satuan = $konfigurasi->mode === PembimbingAkademikMode::PER_KELAS ? 'kelas' : 'mahasiswa';

                                    return new HtmlString(
                                        '<div class="rounded-xl bg-success-50 dark:bg-success-500/10 p-4 text-sm text-success-700 dark:text-success-400 flex items-start gap-3 border border-success-200 dark:border-success-500/20">
                                            <svg class="h-5 w-5 shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                                            <div>Mode: <strong class="font-semibold">' . e($konfigurasi->mode->getLabel()) . '</strong><br/>Terdapat <strong class="font-semibold">' . $sisa . '</strong> ' . $satuan . ' yang belum memiliki Dosen Wali aktif.</div>
                                        </div>'
                                    );
                                }),
                        ]),

                    // Langkah 2: Distribusi Dosen
                    Section::make('2. Pengaturan & Distribusi Dosen')
                        ->description('Pilih dosen pembimbing dan atur detail penugasan (SK).')
                        ->icon('heroicon-o-users')
                        ->columnSpan(['default' => 1, 'lg' => 2])
                        ->schema([
                            Grid::make(2)->schema([
                                Select::make('semester_mulai_id')
                                    ->label('Semester Mulai')
                                    ->searchable()
                                    ->options(fn() => RefTahunAkademik::query()->orderByDesc('id')->pluck('nama_tahun', 'id'))
                                    ->required(),

                                DatePicker::make('tanggal_mulai')
                                    ->label('Tanggal Mulai')
                                    ->default(now())
                                    ->required(),

                                TextInput::make('nomor_sk')
                                    ->label('Nomor SK (Opsional)')
                                    ->placeholder('Misal: SK-123/UNIV/2024')
                                    ->maxLength(255),

                                Toggle::make('is_primary')
                                    ->label('Jadikan Pembimbing Utama')
                                    ->inline(false)
                                    ->default(true),
                            ]),

                            // Checkbox List UI Terbaik yang bisa di-scroll
                            CheckboxList::make('dosen_ids')
                                ->label('Pilih Dosen yang Dilibatkan')
                                ->options(fn() => TrxDosen::query()
                                    ->get()
                                    ->mapWithKeys(fn(TrxDosen $d) => [$d->id => "{$d->person?->nama_lengkap} ({$d->nidn})"]))
                                ->searchable()
                                ->bulkToggleable()
                                ->columns(['default' => 1, 'sm' => 2])
                                ->live()
                                ->afterStateUpdated(fn($livewire) => $livewire->resetGenerate())
                                ->required()
                                ->extraAttributes([
                                    'class' => 'max-h-72 overflow-y-auto border border-gray-200 dark:border-white/10 rounded-xl p-4 bg-gray-50/50 dark:bg-gray-900/50 shadow-inner'
                                ])
                                ->helperText('Gunakan "Select All" untuk memilih semua dosen dengan cepat. Sistem akan membagi rata target ke dosen yang dicentang.'),
                        ]),
                ]),
            ])
            ->statePath('data');
    }

    public function generatePreview(): void
    {
        $state = $this->form->getState();
        $service = app(PembimbingAkademikService::class);

        $konfigurasi = $service->konfigurasiAktif($state['prodi_id'] ?? null, $state['angkatan_id'] ?? null);

        if (! $konfigurasi) {
            Notification::make()->title('Gagal: Konfigurasi belum aktif')->warning()->send();
            return;
        }

        $dosenIds = array_values($state['dosen_ids'] ?? []);

        if ($dosenIds === []) {
            Notification::make()->title('Gagal: Pilih minimal satu dosen')->warning()->send();
            return;
        }

        $dosenOptions = TrxDosen::query()
            ->whereIn('id', $dosenIds)
            ->get()
            ->mapWithKeys(fn(TrxDosen $d) => [$d->id => "{$d->person?->nama_lengkap}"])
            ->all();

        if ($konfigurasi->mode === PembimbingAkademikMode::PER_KELAS) {
            $kelasIds = $service->kelasBelumPunyaWali((int) $state['prodi_id'], (int) $state['angkatan_id'])->keys();
            $targets = Kelas::query()->whereIn('id', $kelasIds)->orderBy('nama_kelas')->get();

            $rows = $targets->values()->map(function ($kelas, $i) use ($dosenIds) {
                return [
                    'target_type' => 'KELAS',
                    'target_id' => $kelas->id,
                    'target_label' => $kelas->nama_kelas,
                    'dosen_id' => $dosenIds[$i % count($dosenIds)],
                ];
            });
        } else {
            $targets = Mahasiswa::query()
                ->where('prodi_id', $state['prodi_id'])
                ->where('angkatan_id', $state['angkatan_id'])
                ->whereNull('deleted_at')
                ->whereDoesntHave('pembimbingAkademik', fn($q) => $q
                    ->where('jenis', PembimbingAkademikJenis::DOSEN_WALI)
                    ->where('status', 'AKTIF'))
                ->get();

            $rows = $targets->values()->map(function ($mhs, $i) use ($dosenIds) {
                return [
                    'target_type' => 'MAHASISWA',
                    'target_id' => $mhs->id,
                    'target_label' => $mhs->nim . ' - ' . $mhs->person?->nama_lengkap,
                    'dosen_id' => $dosenIds[$i % count($dosenIds)],
                ];
            });
        }

        $this->preview = $rows->map(fn(array $row) => [...$row, 'dosen_options' => $dosenOptions])->all();
        $this->previewGenerated = true;
        $this->processed = 0;
        $this->totalGagal = 0;

        if ($this->preview === []) {
            Notification::make()
                ->title('Semua Selesai 🎉')
                ->body('Tidak ada target yang perlu ditugaskan. Semua kelas/mahasiswa pada kombinasi ini sudah memiliki Dosen Wali aktif.')
                ->success()
                ->send();
        }
    }

    /**
     * Dihitung secara real-time setiap kali dosen di dropdown diubah
     */
    public function getWorkloadSummary(): array
    {
        if (empty($this->preview)) {
            return [];
        }

        $dosenOptions = $this->preview[0]['dosen_options'] ?? [];
        $counts = array_count_values(array_column($this->preview, 'dosen_id'));

        $summary = [];
        foreach ($counts as $dId => $count) {
            $summary[] = [
                'nama' => $dosenOptions[$dId] ?? 'Dosen Tidak Diketahui',
                'count' => $count,
            ];
        }

        // Urutkan dari beban terbanyak
        usort($summary, fn($a, $b) => $b['count'] <=> $a['count']);

        return $summary;
    }

    public function processBatch(int $batchSize = 10): array
    {
        $state = $this->form->getState();
        $service = app(PembimbingAkademikService::class);

        $slice = collect($this->preview)->slice($this->processed, $batchSize);

        foreach ($slice as $row) {
            try {
                $service->tugaskan([
                    'jenis' => PembimbingAkademikJenis::DOSEN_WALI->value,
                    'kelas_id' => $row['target_type'] === 'KELAS' ? $row['target_id'] : null,
                    'mahasiswa_id' => $row['target_type'] === 'MAHASISWA' ? $row['target_id'] : null,
                    'dosen_id' => $row['dosen_id'],
                    'is_primary' => $state['is_primary'] ?? true,
                    'semester_mulai_id' => $state['semester_mulai_id'],
                    'tanggal_mulai' => $state['tanggal_mulai'],
                    'nomor_sk' => $state['nomor_sk'] ?? null,
                    'prodi_id' => $state['prodi_id'],
                    'angkatan_id' => $state['angkatan_id'],
                ]);
            } catch (PembimbingAkademikException) {
                $this->totalGagal++;
            }
            $this->processed++;
        }

        $total = count($this->preview);
        $done = $this->processed >= $total;

        if ($done) {
            $berhasil = $total - $this->totalGagal;
            Notification::make()
                ->title('Generate Massal Selesai!')
                ->body("Berhasil: {$berhasil} penugasan. Dilewati: {$this->totalGagal} (sudah memiliki wali aktif).")
                ->success()
                ->persistent()
                ->send();
        }

        return ['done' => $done, 'processed' => $this->processed, 'total' => $total];
    }

    public function resetGenerate(): void
    {
        $this->preview = [];
        $this->previewGenerated = false;
        $this->processed = 0;
        $this->totalGagal = 0;
    }
}
