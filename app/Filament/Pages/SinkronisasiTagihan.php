<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Models\Mahasiswa;
use App\Models\RefProdi;
use App\Models\RefTahunAkademik;
use App\Services\Keuangan\SinkronisasiTagihanService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class SinkronisasiTagihan extends Page implements HasSchemas
{
    use HasPageShield;
    use InteractsWithSchemas;
    use InteractsWithFormActions;

    protected static ?string $navigationLabel = 'Sinkronisasi Tagihan';
    protected static ?string $title = 'Sinkronisasi Komponen Tagihan';
    protected static ?int $navigationSort = 7;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::KEUANGAN->value;
    protected string $view = 'filament.pages.sinkronisasi-tagihan';

    public ?array $data = [];

    /**
     * Hasil preview tersimpan di state Livewire, BUKAN dihitung ulang di
     * setiap render form seperti hint() pada GeneratorTagihan - karena
     * perbandingan skema vs tagihan existing jauh lebih berat daripada
     * sekadar count(), jadi hanya dijalankan saat admin eksplisit menekan
     * tombol "Jalankan Preview".
     */
    public ?array $previewResult = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Parameter Sinkronisasi')
                    ->description('Pilih target mahasiswa yang tagihannya akan disinkronkan terhadap skema tarif aktif. Sinkronisasi hanya memproses mahasiswa yang SUDAH memiliki tagihan reguler di semester ini.')
                    ->schema([
                        Radio::make('tipe_target')
                            ->label('Target')
                            ->options([
                                'kolektif' => 'Kolektif (Berdasarkan Prodi/Angkatan)',
                                'spesifik' => 'Mahasiswa Spesifik',
                            ])
                            ->default('kolektif')
                            ->live()
                            ->afterStateUpdated(fn() => $this->previewResult = null),

                        Select::make('tahun_akademik_id')
                            ->label('Tahun Akademik')
                            ->options(RefTahunAkademik::query()->pluck('nama_tahun', 'id'))
                            ->default(fn() => RefTahunAkademik::where('is_active', true)->value('id'))
                            ->searchable()
                            ->live()
                            ->required()
                            ->afterStateUpdated(fn() => $this->previewResult = null),

                        Select::make('prodi_id')
                            ->label('Program Studi')
                            ->options(RefProdi::query()->pluck('nama_prodi', 'id'))
                            ->searchable()
                            ->visible(fn(Get $get) => $get('tipe_target') === 'kolektif')
                            ->live()
                            ->afterStateUpdated(fn() => $this->previewResult = null),

                        Select::make('angkatan_id')
                            ->label('Angkatan')
                            ->options(Mahasiswa::select('angkatan_id')->distinct()->pluck('angkatan_id', 'angkatan_id'))
                            ->searchable()
                            ->visible(fn(Get $get) => $get('tipe_target') === 'kolektif')
                            ->live()
                            ->afterStateUpdated(fn() => $this->previewResult = null),

                        Select::make('mahasiswa_id')
                            ->label('Mahasiswa')
                            ->options(
                                Mahasiswa::query()
                                    ->join('ref_person', 'mahasiswas.person_id', '=', 'ref_person.id')
                                    ->select('mahasiswas.id', 'mahasiswas.nim', 'ref_person.nama_lengkap')
                                    ->get()
                                    ->mapWithKeys(fn($mhs) => [$mhs->id => "{$mhs->nim} - {$mhs->nama_lengkap}"])
                            )
                            ->searchable()
                            ->visible(fn(Get $get) => $get('tipe_target') === 'spesifik')
                            ->required(fn(Get $get) => $get('tipe_target') === 'spesifik')
                            ->live()
                            ->afterStateUpdated(fn() => $this->previewResult = null),
                    ])
                    ->columns(2),

                Section::make('Statistik Preview')
                    ->description('Tekan "Jalankan Preview" pada aksi di bawah untuk menghitung dampak sinkronisasi tanpa mengubah data apa pun.')
                    ->visible(fn() => $this->previewResult !== null)
                    ->schema([
                        Grid::make(5)->schema([
                            TextEntry::make('total_mahasiswa_target')
                                ->label('Mahasiswa Diperiksa')
                                ->state(fn() => $this->previewResult['agregat']['total_mahasiswa_target'] ?? 0),
                            TextEntry::make('jumlah_ditambah')
                                ->label('Komponen Akan Ditambah')
                                ->badge()->color('success')
                                ->state(fn() => $this->previewResult['agregat']['jumlah_ditambah'] ?? 0),
                            TextEntry::make('jumlah_review')
                                ->label('Perlu Review')
                                ->badge()->color('warning')
                                ->state(fn() => $this->previewResult['agregat']['jumlah_review'] ?? 0),
                            TextEntry::make('jumlah_warning')
                                ->label('Warning (Tidak Ada di Skema)')
                                ->badge()->color('danger')
                                ->state(fn() => $this->previewResult['agregat']['jumlah_warning'] ?? 0),
                            TextEntry::make('total_tanpa_tagihan')
                                ->label('Dilewati (Belum Ada Tagihan)')
                                ->state(fn() => $this->previewResult['agregat']['total_tanpa_tagihan'] ?? 0),
                        ]),
                        TextEntry::make('catatan_dibatasi')
                            ->hiddenLabel()
                            ->visible(fn() => (bool) ($this->previewResult['dibatasi'] ?? false))
                            ->color('warning')
                            ->state('Daftar di bawah dibatasi 100 baris pertama per kategori. Gunakan "Export CSV" untuk daftar lengkap.'),
                    ]),

                Section::make('Daftar Komponen Baru')
                    ->visible(fn() => $this->previewResult !== null && count($this->previewResult['sampel_tambah'] ?? []) > 0)
                    ->schema([
                        RepeatableEntry::make('sampel_tambah')
                            ->hiddenLabel()
                            ->state(fn() => $this->previewResult['sampel_tambah'] ?? [])
                            ->schema([
                                TextEntry::make('nim')->label('NIM'),
                                TextEntry::make('nama')->label('Nama'),
                                TextEntry::make('nama_komponen')->label('Komponen'),
                                TextEntry::make('nominal')->label('Nominal')->money('IDR'),
                            ])
                            ->columns(4),
                    ]),

                Section::make('Daftar Komponen Berubah (Review)')
                    ->description('Nominal TIDAK diubah otomatis. Tindak lanjuti lewat halaman Riwayat Sinkronisasi setelah eksekusi selesai.')
                    ->visible(fn() => $this->previewResult !== null && count($this->previewResult['sampel_review'] ?? []) > 0)
                    ->schema([
                        RepeatableEntry::make('sampel_review')
                            ->hiddenLabel()
                            ->state(fn() => $this->previewResult['sampel_review'] ?? [])
                            ->schema([
                                TextEntry::make('nim')->label('NIM'),
                                TextEntry::make('nama')->label('Nama'),
                                TextEntry::make('nama_komponen')->label('Komponen'),
                                TextEntry::make('nominal_existing')->label('Nominal Lama')->money('IDR'),
                                TextEntry::make('nominal_skema_baru')->label('Nominal Skema Saat Ini')->money('IDR'),
                            ])
                            ->columns(5),
                    ]),

                Section::make('Warning: Komponen Tidak Ada di Skema Aktif')
                    ->description('Komponen ini TIDAK dihapus dari tagihan. Tinjau manual apakah skema tarif perlu diperbarui atau komponen ini memang sengaja dihentikan.')
                    ->visible(fn() => $this->previewResult !== null && count($this->previewResult['sampel_warning'] ?? []) > 0)
                    ->schema([
                        RepeatableEntry::make('sampel_warning')
                            ->hiddenLabel()
                            ->state(fn() => $this->previewResult['sampel_warning'] ?? [])
                            ->schema([
                                TextEntry::make('nim')->label('NIM'),
                                TextEntry::make('nama')->label('Nama'),
                                TextEntry::make('nama_komponen_snapshot')->label('Komponen'),
                                TextEntry::make('nominal_existing')->label('Nominal di Tagihan')->money('IDR'),
                            ])
                            ->columns(4),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('jalankan_preview')
                ->label('Jalankan Preview')
                ->color('gray')
                ->action('jalankanPreview'),

            Action::make('export_csv')
                ->label('Export Preview (CSV)')
                ->color('gray')
                ->action('exportCsv'),

            Action::make('dry_run')
                ->label('Dry Run')
                ->color('warning')
                ->disabled(fn() => $this->previewResult === null)
                ->requiresConfirmation()
                ->modalDescription('Dry run menjalankan proses sinkronisasi secara penuh (sama seperti eksekusi sungguhan) TAPI tidak menulis perubahan apa pun ke database. Hasilnya bisa dilihat di Riwayat Sinkronisasi.')
                ->action(fn() => $this->eksekusi(dryRun: true)),

            Action::make('jalankan_sinkronisasi')
                ->formId('form-sinkronisasi-tagihan')
                ->disabled(fn() => ! auth()->user()->can('SinkronisasiTagihan') || $this->previewResult === null)
                ->label('Jalankan Sinkronisasi 🔄')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Sinkronisasi Tagihan')
                ->modalDescription('Proses ini akan MENAMBAHKAN komponen baru ke tagihan mahasiswa yang sudah ada, dan mencatat temuan perubahan nominal ke daftar review. Tagihan, detail, dan histori pembayaran yang sudah ada TIDAK akan dihapus atau diubah.')
                ->modalSubmitActionLabel('Lanjutkan')
                ->action(fn() => $this->eksekusi(dryRun: false)),
        ];
    }

    public function jalankanPreview(SinkronisasiTagihanService $service): void
    {
        $data = $this->form->getState();

        if (empty($data['tahun_akademik_id'])) {
            Notification::make()->title('Pilih Tahun Akademik terlebih dahulu')->warning()->send();
            return;
        }

        $this->previewResult = $service->preview($data);
    }

    public function exportCsv(SinkronisasiTagihanService $service): void
    {
        $data = $this->form->getState();
        if (empty($data['tahun_akademik_id'])) {
            Notification::make()->title('Pilih Tahun Akademik terlebih dahulu')->warning()->send();
            return;
        }

        // Dilempar ke queue (ExportSinkronisasiPreviewJob) supaya tidak
        // memblokir request untuk target dengan jumlah mahasiswa besar.
        // Admin akan menerima notifikasi berisi link unduh saat selesai.
        $result = $service->requestExportPreview($data, Auth::id());

        Notification::make()
            ->title('Export Dimasukkan ke Antrean')
            ->body($result['message'])
            ->info()
            ->send();
    }

    protected function eksekusi(bool $dryRun): void
    {
        abort_unless($dryRun || auth()->user()->can('SinkronisasiTagihan'), 403);

        $data = $this->form->getState();
        $service = app(SinkronisasiTagihanService::class);
        $result = $service->jalankan($data, Auth::id(), dryRun: $dryRun);

        Notification::make()
            ->title($dryRun ? 'Dry Run Dimulai' : 'Sinkronisasi Dimulai')
            ->body($result['message'])
            ->info()
            ->send();

        $this->previewResult = null;
    }
}
