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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

/**
 * Generate Penugasan Dosen Wali Massal.
 *
 * Halaman ini sengaja TIDAK memakai Filament\Schemas\Components\Wizard
 * bawaan, karena desain "Academic Control Center" butuh panel dampak
 * (live impact) yang persisten di samping form — sesuatu yang tidak
 * bisa disisipkan di antara Step milik Wizard. Sebagai gantinya, step
 * dikendalikan sendiri lewat $currentStep, dan tiap field schema
 * disembunyikan/ditampilkan lewat ->visible() sesuai step aktif.
 */
class GeneratePenugasanMassalPage extends Page implements HasForms
{
    use HasPageShield;
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationLabel = 'Generate Massal';

    protected static ?string $title = 'Generate Penugasan Dosen Wali Massal';

    protected static ?int $navigationSort = 2;

    protected static ?string $cluster = PembimbingAkademikCluster::class;

    protected string $view = 'filament.clusters.pembimbing-akademik.pages.generate-penugasan-massal-page';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public int $currentStep = 1;

    /**
     * @var null|array{mode: string, mode_label: string, satuan: string, sisa: int, total: int}
     */
    public ?array $konfigurasi = null;

    /** @var array<int, array{dosen_id: int, nama: string, nidn: string, jumlah: int}> */
    public array $distribusi = [];

    /** @var array<int, array<string, mixed>> */
    public array $preview = [];

    /** @var array<int, array{dosen_id: int, nama: string, nidn: string, rows: array<int, array<string, mixed>>}> */
    public array $previewGrouped = [];

    /** @var array<int, string> */
    public array $dosenOptions = [];

    public bool $previewGenerated = false;

    public bool $isProcessing = false;

    public int $processed = 0;

    public int $totalGagal = 0;

    /** @var array<int, array{label: string, status: 'ok'|'gagal'}> */
    public array $liveFeed = [];

    public function mount(): void
    {
        $this->form->fill([
            'is_primary' => true,
            'tanggal_mulai' => now()->toDateString(),
            'semester_mulai_id' => RefTahunAkademik::query()->where('is_active', true)->value('id'),
        ]);
    }

    /**
     * @return array<int, string>
     */
    public function getSteps(): array
    {
        return [
            1 => 'Ruang Lingkup',
            2 => 'Distribusi Dosen',
            3 => 'Preview',
            4 => 'Selesai',
        ];
    }

    public function form(Schema $schema): Schema
    {
        // Ditangkap ke variabel lokal supaya closure schema (yang dieksekusi
        // lewat app()->call()) tetap bisa membaca state Livewire terkini.
        $page = $this;

        return $schema
            ->components([
                Select::make('prodi_id')
                    ->label('Program Studi')
                    ->options(fn() => RefProdi::query()->orderBy('nama_prodi')->pluck('nama_prodi', 'id'))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn() => $page->refreshKonfigurasi())
                    ->required()
                    ->visible(fn() => $page->currentStep === 1),

                Select::make('angkatan_id')
                    ->label('Angkatan')
                    ->options(fn() => RefAngkatan::query()->orderByDesc('id_tahun')->pluck('id_tahun', 'id_tahun'))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn() => $page->refreshKonfigurasi())
                    ->required()
                    ->visible(fn() => $page->currentStep === 1),

                CheckboxList::make('dosen_ids')
                    ->label('Dosen yang Dilibatkan')
                    ->options(fn($get) => TrxDosen::query()
                        ->when($get('prodi_id'), fn($q, $prodiId) => $q->where('prodi_id', $prodiId))
                        ->get()
                        ->mapWithKeys(fn(TrxDosen $d) => [$d->id => "{$d->person?->nama_lengkap} ({$d->nidn})"]))
                    ->searchable()
                    ->bulkToggleable()
                    ->columns(2)
                    ->live()
                    ->afterStateUpdated(fn() => $page->refreshDistribusi())
                    ->required()
                    ->helperText('Beban dibagi merata (round-robin) berdasarkan urutan dipilih. Masih bisa diubah per-baris di layar Preview.')
                    ->visible(fn() => $page->currentStep === 2),

                Select::make('semester_mulai_id')
                    ->label('Semester Mulai')
                    ->searchable()
                    ->options(fn() => RefTahunAkademik::query()->orderByDesc('id')->pluck('nama_tahun', 'id'))
                    ->required()
                    ->visible(fn() => $page->currentStep === 2),

                DatePicker::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->default(now())
                    ->required()
                    ->visible(fn() => $page->currentStep === 2),

                TextInput::make('nomor_sk')
                    ->label('Nomor SK (berlaku untuk semua)')
                    ->maxLength(255)
                    ->visible(fn() => $page->currentStep === 2),

                Toggle::make('is_primary')
                    ->label('Sebagai Pembimbing Utama')
                    ->default(true)
                    ->visible(fn() => $page->currentStep === 2),
            ])
            ->statePath('data');
    }

    /**
     * Dipanggil tiap prodi/angkatan berubah — mengisi panel "Live Impact"
     * di sisi kanan (jumlah target yang belum punya wali, mode aktif).
     */
    public function refreshKonfigurasi(): void
    {
        $this->konfigurasi = null;
        $this->distribusi = [];

        $prodiId = $this->data['prodi_id'] ?? null;
        $angkatanId = $this->data['angkatan_id'] ?? null;

        if (! $prodiId || ! $angkatanId) {
            return;
        }

        $service = app(PembimbingAkademikService::class);
        $konfigurasi = $service->konfigurasiAktif($prodiId, $angkatanId);

        if (! $konfigurasi) {
            return;
        }

        $isPerKelas = $konfigurasi->mode === PembimbingAkademikMode::PER_KELAS;

        $sisa = $isPerKelas
            ? $service->kelasBelumPunyaWali((int) $prodiId, (int) $angkatanId)->count()
            : Mahasiswa::query()
            ->where('prodi_id', $prodiId)
            ->where('angkatan_id', $angkatanId)
            ->whereNull('deleted_at')
            ->whereDoesntHave('pembimbingAkademik', fn($q) => $q
                ->where('jenis', PembimbingAkademikJenis::DOSEN_WALI)
                ->where('status', 'AKTIF'))
            ->count();

        $total = $isPerKelas
            ? Kelas::query()->where('prodi_id', $prodiId)->where('angkatan_id', $angkatanId)->count()
            : Mahasiswa::query()->where('prodi_id', $prodiId)->where('angkatan_id', $angkatanId)->whereNull('deleted_at')->count();

        $this->konfigurasi = [
            'mode' => $konfigurasi->mode->value,
            'mode_label' => $konfigurasi->mode->getLabel(),
            'satuan' => $isPerKelas ? 'kelas' : 'mahasiswa',
            'sisa' => $sisa,
            'total' => $total,
        ];
    }

    /**
     * Dipanggil tiap daftar dosen berubah — mengisi perkiraan bar
     * distribusi beban di panel kanan step 2, sebelum preview asli dibuat.
     */
    public function refreshDistribusi(): void
    {
        $this->distribusi = [];

        $dosenIds = array_values($this->data['dosen_ids'] ?? []);
        $sisa = $this->konfigurasi['sisa'] ?? 0;

        if ($dosenIds === [] || $sisa === 0) {
            return;
        }

        $dosen = TrxDosen::query()->whereIn('id', $dosenIds)->get()->keyBy('id');

        $jumlahDosen = count($dosenIds);
        $base = intdiv($sisa, $jumlahDosen);
        $sisaBagi = $sisa % $jumlahDosen;

        $this->distribusi = collect($dosenIds)
            ->values()
            ->map(fn(int $id, int $i) => [
                'dosen_id' => $id,
                'nama' => $dosen->get($id)?->person?->nama_lengkap ?? '—',
                'nidn' => $dosen->get($id)?->nidn ?? '',
                'jumlah' => $base + ($i < $sisaBagi ? 1 : 0),
            ])
            ->all();
    }

    /**
     * Navigasi step dengan validasi ringan di setiap gerbang, supaya user
     * tidak bisa melompat ke Preview/Distribusi dalam keadaan tidak valid.
     */
    public function goToStep(int $step): void
    {
        if ($step === 2) {
            $this->validateOnly('data.prodi_id');
            $this->validateOnly('data.angkatan_id');

            if (! $this->konfigurasi) {
                Notification::make()
                    ->title('Konfigurasi belum aktif untuk kombinasi ini')
                    ->body('Atur konfigurasi mode pembimbingan terlebih dahulu di menu Konfigurasi Pembimbing.')
                    ->warning()
                    ->send();

                return;
            }

            if ($this->konfigurasi['sisa'] === 0) {
                Notification::make()
                    ->title('Tidak ada target yang perlu ditugaskan')
                    ->body("Semua {$this->konfigurasi['satuan']} pada kombinasi ini sudah memiliki Dosen Wali aktif.")
                    ->info()
                    ->send();

                return;
            }
        }

        if ($step === 3) {
            $this->validateOnly('data.dosen_ids');
            $this->generatePreview();

            if ($this->preview === []) {
                return;
            }
        }

        $this->currentStep = $step;
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

        $this->dosenOptions = TrxDosen::query()
            ->whereIn('id', $dosenIds)
            ->get()
            ->mapWithKeys(fn(TrxDosen $d) => [$d->id => "{$d->person?->nama_lengkap} ({$d->nidn})"])
            ->all();

        $this->preview = $rows->values()->all();
        $this->previewGenerated = true;
        $this->processed = 0;
        $this->totalGagal = 0;
        $this->liveFeed = [];

        $this->rebuildGroupedPreview();

        if ($this->preview === []) {
            Notification::make()
                ->title('Tidak ada target yang perlu ditugaskan')
                ->body('Semua kelas/mahasiswa pada kombinasi ini sudah memiliki Dosen Wali aktif.')
                ->info()
                ->send();
        }
    }

    /**
     * Ganti dosen untuk satu baris preview langsung dari tabel (tanpa
     * mengulang seluruh generate), lalu susun ulang pengelompokan.
     */
    public function reassignTarget(int $index, int $dosenId): void
    {
        if (! isset($this->preview[$index])) {
            return;
        }

        $this->preview[$index]['dosen_id'] = $dosenId;
        $this->rebuildGroupedPreview();
    }

    protected function rebuildGroupedPreview(): void
    {
        $dosen = TrxDosen::query()
            ->whereIn('id', array_keys($this->dosenOptions))
            ->get()
            ->keyBy('id');

        $this->previewGrouped = collect($this->preview)
            ->groupBy('dosen_id')
            ->map(fn(Collection $rows, int $dosenId) => [
                'dosen_id' => $dosenId,
                'nama' => $dosen->get($dosenId)?->person?->nama_lengkap ?? '—',
                'nidn' => $dosen->get($dosenId)?->nidn ?? '',
                'rows' => $rows->values()->all(),
            ])
            ->values()
            ->all();
    }

    public function startProcessing(): void
    {
        $this->isProcessing = true;
        $this->processed = 0;
        $this->totalGagal = 0;
        $this->liveFeed = [];
    }

    /**
     * Diproses per-batch, dipanggil berulang dari Alpine sampai selesai —
     * supaya progress bar & live feed naik secara nyata, bukan sekadar
     * spinner tunggal menunggu satu request besar.
     *
     * @return array{done: bool, processed: int, total: int}
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

                $this->pushLiveFeed($row['target_label'], 'ok');
            } catch (PembimbingAkademikException) {
                $this->totalGagal++;
                $this->pushLiveFeed($row['target_label'], 'gagal');
            }

            $this->processed++;
        }

        $total = count($this->preview);
        $done = $this->processed >= $total;

        if ($done) {
            $this->isProcessing = false;
            $this->currentStep = 4;

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

    /**
     * Menghentikan sisa batch dari sisi client. Baris yang sudah
     * ter-commit sebelum tombol ini ditekan TIDAK dibatalkan/rollback —
     * label tombol & pesan di Blade harus jujur soal ini.
     */
    public function stopProcessing(): void
    {
        $this->isProcessing = false;

        Notification::make()
            ->title('Proses dihentikan')
            ->body("{$this->processed} dari " . count($this->preview) . ' target sudah diproses sebelum dihentikan. Penugasan yang sudah dibuat tidak dibatalkan.')
            ->warning()
            ->send();
    }

    public function resetGenerate(): void
    {
        $this->preview = [];
        $this->previewGrouped = [];
        $this->previewGenerated = false;
        $this->processed = 0;
        $this->totalGagal = 0;
        $this->liveFeed = [];
        $this->isProcessing = false;
        $this->currentStep = 1;
    }
}
