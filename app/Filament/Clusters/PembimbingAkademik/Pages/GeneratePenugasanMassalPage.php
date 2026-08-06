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
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class GeneratePenugasanMassalPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationLabel = 'Generate Massal';

    protected static ?string $title = 'Generate Penugasan Dosen Wali Massal';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.clusters.pembimbing-akademik.pages.generate-penugasan-massal-page';

    protected static ?string $cluster = PembimbingAkademikCluster::class;
    /** @var array<string, mixed> */
    public ?array $data = [];

    /** @var array<int, array<string, mixed>> */
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
                Wizard::make([
                    Step::make('Ruang Lingkup')
                        ->description('Prodi & Angkatan')
                        ->icon('heroicon-o-building-library')
                        ->components([
                            Select::make('prodi_id')
                                ->label('Program Studi')
                                ->options(fn() => RefProdi::query()->orderBy('nama_prodi')->pluck('nama_prodi', 'id'))
                                ->searchable()
                                ->live()
                                ->required(),
                            Select::make('angkatan_id')
                                ->label('Angkatan')
                                ->options(fn() => RefAngkatan::query()->orderByDesc('id_tahun')->pluck('id_tahun', 'id_tahun'))
                                ->searchable()
                                ->live()
                                ->required(),
                            Placeholder::make('info_konfigurasi')
                                ->label('')
                                ->visible(fn($get) => $get('prodi_id') && $get('angkatan_id'))
                                ->content(function ($get) {
                                    $service = app(PembimbingAkademikService::class);
                                    $konfigurasi = $service->konfigurasiAktif($get('prodi_id'), $get('angkatan_id'));

                                    if (! $konfigurasi) {
                                        return new HtmlString(
                                            '<div class="rounded-lg bg-danger-50 dark:bg-danger-500/10 p-3 text-sm text-danger-700 dark:text-danger-400">
                                                ⚠️ Belum ada konfigurasi aktif untuk kombinasi ini. Generate massal tidak bisa dilanjutkan — atur dulu di menu Konfigurasi Pembimbing.
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
                                        '<div class="rounded-lg bg-success-50 dark:bg-success-500/10 p-3 text-sm text-success-700 dark:text-success-400">
                                            ✅ Mode: <strong>' . e($konfigurasi->mode->getLabel()) . '</strong> — <strong>' . $sisa . '</strong> ' . $satuan . ' belum memiliki Dosen Wali aktif.
                                        </div>'
                                    );
                                }),
                        ]),

                    Step::make('Distribusi Dosen')
                        ->description('Pilih dosen & detail SK')
                        ->icon('heroicon-o-users')
                        ->components([
                            CheckboxList::make('dosen_ids')
                                ->label('Dosen yang Dilibatkan')
                                ->options(fn($get) => TrxDosen::query()
                                    ->when($get('prodi_id'), fn($q) => $q->where('prodi_id', $get('prodi_id')))
                                    ->get()
                                    ->mapWithKeys(fn(TrxDosen $d) => [$d->id => "{$d->person?->nama_lengkap} ({$d->nidn})"]))
                                ->searchable()
                                ->bulkToggleable()
                                ->columns(2)
                                ->required()
                                ->helperText('Beban dibagi rata (round-robin) ke seluruh target yang belum punya wali sesuai urutan dipilih. Masih bisa diubah per-baris di layar Preview.'),
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
                                ->label('Nomor SK (berlaku untuk semua)')
                                ->maxLength(255),
                            Toggle::make('is_primary')
                                ->label('Sebagai Pembimbing Utama')
                                ->default(true),
                        ]),
                ])->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function generatePreview(): void
    {
        $state = $this->form->getState();
        $service = app(PembimbingAkademikService::class);

        $konfigurasi = $service->konfigurasiAktif($state['prodi_id'] ?? null, $state['angkatan_id'] ?? null);

        if (! $konfigurasi) {
            Notification::make()->title('Konfigurasi belum aktif untuk kombinasi ini')->warning()->send();

            return;
        }

        $dosenIds = array_values($state['dosen_ids'] ?? []);

        if ($dosenIds === []) {
            Notification::make()->title('Pilih minimal satu dosen')->warning()->send();

            return;
        }

        if ($konfigurasi->mode === PembimbingAkademikMode::PER_KELAS) {
            $kelasIds = $service->kelasBelumPunyaWali((int) $state['prodi_id'], (int) $state['angkatan_id'])->keys();
            $targets = Kelas::query()->whereIn('id', $kelasIds)->orderBy('nama_kelas')->get();

            $rows = $targets->values()->map(fn($kelas, $i) => [
                'target_type' => 'KELAS',
                'target_id' => $kelas->id,
                'target_label' => $kelas->nama_kelas,
                'dosen_id' => $dosenIds[$i % count($dosenIds)],
            ]);
        } else {
            $targets = Mahasiswa::query()
                ->where('prodi_id', $state['prodi_id'])
                ->where('angkatan_id', $state['angkatan_id'])
                ->whereNull('deleted_at')
                ->whereDoesntHave('pembimbingAkademik', fn($q) => $q
                    ->where('jenis', PembimbingAkademikJenis::DOSEN_WALI)
                    ->where('status', 'AKTIF'))
                ->get();

            $rows = $targets->values()->map(fn($mhs, $i) => [
                'target_type' => 'MAHASISWA',
                'target_id' => $mhs->id,
                'target_label' => $mhs->nim . ' - ' . $mhs->person?->nama_lengkap,
                'dosen_id' => $dosenIds[$i % count($dosenIds)],
            ]);
        }

        $dosenOptions = TrxDosen::query()
            ->whereIn('id', $dosenIds)
            ->get()
            ->mapWithKeys(fn(TrxDosen $d) => [$d->id => "{$d->person?->nama_lengkap} ({$d->nidn})"])
            ->all();

        $this->preview = $rows->map(fn(array $row) => [...$row, 'dosen_options' => $dosenOptions])->all();
        $this->previewGenerated = true;
        $this->processed = 0;
        $this->totalGagal = 0;

        if ($this->preview === []) {
            Notification::make()
                ->title('Tidak ada target yang perlu ditugaskan')
                ->body('Semua kelas/mahasiswa pada kombinasi ini sudah memiliki Dosen Wali aktif.')
                ->info()
                ->send();
        }
    }

    /**
     * Diproses per-batch dipanggil berulang dari JS (Alpine) sampai
     * selesai — supaya progress bar naik secara nyata, bukan sekadar
     * spinner tunggal menunggu satu request besar.
     */
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
                ->title('Generate massal selesai')
                ->body("{$berhasil} penugasan berhasil dibuat, {$this->totalGagal} dilewati (sudah ada wali aktif).")
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
