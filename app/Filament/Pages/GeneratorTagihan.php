<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Filament\Resources\GeneratorBatches\GeneratorBatchResource;
use App\Models\Mahasiswa;
use App\Models\RefProdi;
use App\Models\RefTahunAkademik;
use App\Services\TagihanService;
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
use Filament\Schemas\Concerns\RestrictsFileUploadsToSchemaComponents;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use UnitEnum;

class GeneratorTagihan extends Page implements HasSchemas
{
    use HasPageShield;
    use InteractsWithSchemas;
    use InteractsWithFormActions;
    use RestrictsFileUploadsToSchemaComponents;

    protected static ?string $navigationLabel = 'Generator Tagihan Reguler';
    protected static ?string $title = 'Generator Tagihan Mahasiswa';
    protected static ?int $navigationSort = 5;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::KEUANGAN->value;
    protected string $view = 'filament.pages.generator-tagihan';

    public ?array $data = [];

    /**
     * Sama seperti Sinkronisasi: preview dihitung SEKALI saat admin
     * eksplisit menekan tombol "Jalankan Preview", bukan di setiap render
     * form (yang tadinya jadi masalah performa di versi hint() sebelumnya
     * untuk data besar).
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
                Section::make('Parameter Generate Tagihan')
                    ->description('Pilih kriteria mahasiswa yang akan dibuatkan tagihan, lalu jalankan Preview sebelum generate.')
                    ->schema([
                        Radio::make('tipe_target')
                            ->label('Target Generate')
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
                    ->description('Tekan "Jalankan Preview" pada aksi di bawah untuk melihat dampak generate tanpa membuat tagihan apa pun.')
                    ->visible(fn() => $this->previewResult !== null)
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('total_mahasiswa_kriteria')
                                ->label('Total Sesuai Kriteria')
                                ->state(fn() => $this->previewResult['agregat']['total_mahasiswa_kriteria'] ?? 0),
                            TextEntry::make('siap_digenerate')
                                ->label('Siap Digenerate')
                                ->badge()->color('success')
                                ->state(fn() => $this->previewResult['agregat']['siap_digenerate'] ?? 0),
                            TextEntry::make('sudah_punya_tagihan')
                                ->label('Dilewati (Sudah Ada Tagihan)')
                                ->badge()->color('gray')
                                ->state(fn() => $this->previewResult['agregat']['sudah_punya_tagihan'] ?? 0),
                            TextEntry::make('akan_gagal_tanpa_skema')
                                ->label('Berpotensi Gagal')
                                ->badge()->color('danger')
                                ->state(fn() => $this->previewResult['agregat']['akan_gagal_tanpa_skema'] ?? 0),
                        ]),
                        TextEntry::make('catatan_dibatasi')
                            ->hiddenLabel()
                            ->visible(fn() => (bool) ($this->previewResult['dibatasi'] ?? false))
                            ->color('warning')
                            ->state('Daftar & rincian nominal di bawah dibatasi 100 baris pertama per kategori (bukan estimasi total seluruh batch) - dihitung untuk sampel supaya preview tetap ringan pada target berjumlah besar.'),
                    ]),

                Section::make('Mahasiswa Berpotensi Gagal')
                    ->description('Mahasiswa ini TIDAK akan mendapat tagihan kalau Generate dijalankan sekarang - program_id kosong atau skema tarif belum dikonfigurasi. Perbaiki data ini dulu sebelum generate, atau lanjutkan (mahasiswa ini otomatis dilewati job dan dicatat sebagai error).')
                    ->visible(fn() => $this->previewResult !== null && count($this->previewResult['sampel_gagal'] ?? []) > 0)
                    ->schema([
                        RepeatableEntry::make('sampel_gagal')
                            ->hiddenLabel()
                            ->state(fn() => $this->previewResult['sampel_gagal'] ?? [])
                            ->schema([
                                TextEntry::make('nim')->label('NIM'),
                                TextEntry::make('nama')->label('Nama'),
                                TextEntry::make('alasan')->label('Alasan')->color('danger'),
                            ])
                            ->columns(3),
                    ]),

                Section::make('Sampel Mahasiswa Siap Digenerate')
                    ->description('Total tagihan sudah memperhitungkan diskon beasiswa (kalau ada), sama seperti yang akan tercatat saat Generate benar-benar dijalankan.')
                    ->visible(fn() => $this->previewResult !== null && count($this->previewResult['sampel_siap'] ?? []) > 0)
                    ->collapsible()
                    ->collapsed(fn() => ! collect($this->previewResult['sampel_siap'] ?? [])->contains(fn($item) => filled($item['status_warning'] ?? null)))
                    ->schema([
                        RepeatableEntry::make('sampel_siap')
                            ->hiddenLabel()
                            ->state(fn() => $this->previewResult['sampel_siap'] ?? [])
                            ->schema([
                                TextEntry::make('status_warning')
                                    ->hiddenLabel()
                                    ->visible(fn($state) => filled($state))
                                    ->badge()
                                    ->color('danger')
                                    ->icon('heroicon-m-exclamation-triangle')
                                    ->columnSpanFull(),
                                Grid::make(5)->schema([
                                    TextEntry::make('nim')->label('NIM'),
                                    TextEntry::make('nama')->label('Nama'),
                                    TextEntry::make('jumlah_komponen')->label('Jml Komponen'),
                                    TextEntry::make('total_diskon')->label('Total Diskon')->money('IDR')
                                        ->color(fn($state) => $state > 0 ? 'success' : 'gray'),
                                    TextEntry::make('total_tagihan')->label('Total Tagihan')->money('IDR')
                                        ->weight('bold'),
                                ]),
                                RepeatableEntry::make('rincian_komponen')
                                    ->label('Rincian Komponen')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            TextEntry::make('nama_komponen')->label('Komponen'),
                                            TextEntry::make('nominal_dasar')->label('Nominal Dasar')->money('IDR'),
                                            TextEntry::make('nominal_bersih')->label('Setelah Diskon')->money('IDR'),
                                        ]),
                                    ]),
                            ]),
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

            Action::make('generate_tagihan')
                ->formId('form-generator-tagihan')
                ->disabled(fn() => ! auth()->user()->can('GenerateTagihan') || $this->previewResult === null)
                ->label('Generate Tagihan Sekarang🚀')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Proses Generate Tagihan')
                ->modalDescription('Proses ini akan menghasilkan tagihan mahasiswa sesuai skema tarif yang berlaku. Pastikan data tahun akademik, angkatan, program studi, dan komponen biaya telah dikonfigurasi dengan benar. Setelah proses dijalankan, tagihan akan diterbitkan kepada mahasiswa yang memenuhi kriteria.')
                ->modalSubmitActionLabel('Lanjutkan')
                ->modalCancelActionLabel('Batal')
                ->modalSubmitAction(fn($action) => $action->color('success'))
                ->action(function (TagihanService $service) {
                    abort_unless(auth()->user()->can('GenerateTagihan'), 403);
                    $this->prosesGenerateTagihan($service);
                }),
        ];
    }

    public function jalankanPreview(TagihanService $service): void
    {
        $data = $this->form->getState();

        if (empty($data['tahun_akademik_id'])) {
            Notification::make()->title('Pilih Tahun Akademik terlebih dahulu')->warning()->send();
            return;
        }

        if ($data['tipe_target'] === 'spesifik' && empty($data['mahasiswa_id'])) {
            Notification::make()->title('Pilih Mahasiswa terlebih dahulu')->warning()->send();
            return;
        }

        $this->previewResult = $service->preview($data);
    }

    public function prosesGenerateTagihan(TagihanService $service): void
    {
        $data = $this->form->getState();
        $result = $service->generate($data);

        if ($result['status'] === 'success') {
            Notification::make()
                ->title('Proses Antrean Dimulai')
                ->body($result['message'])
                ->info()
                ->actions([
                  Action::make('lihat_riwayat')
                        ->label('Lihat di Riwayat Generator')
                        ->url(GeneratorBatchResource::getUrl('view', ['record' => $result['batch_id']]))
                        ->button(),
                ])
                ->send();

            $this->previewResult = null;
            $this->form->fill();
        } else {
            Notification::make()
                ->title('Gagal Generate Tagihan')
                ->body('Sistem error: ' . $result['message'])
                ->danger()
                ->send();
        }
    }
}
