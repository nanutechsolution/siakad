<?php

namespace App\Filament\Resources\Kelas\Pages;

use App\Filament\Resources\Kelas\KelasResource;
use App\Models\Mahasiswa;
use App\Models\RefProdi;
use App\Services\KelasGenerationService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class GenerateKelasWizard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = KelasResource::class;
    protected static ?string $title = 'Generate Kelas Otomatis';

    protected string $view = 'filament.resources.kelas.pages.generate-kelas-wizard';
    public ?array $data = [];

    public ?int $prodiId = null;
    public ?int $programId = null;
    public ?int $angkatanId = null;
    public int $jumlahBelumBerkelas = 0;

    public function mount(): void
    {
        $this->prodiId = request()->integer('prodi_id') ?: null;
        $this->programId = request()->integer('program_id') ?: null;
        $this->angkatanId = request()->integer('angkatan_id') ?: null;

        abort_unless(
            $this->prodiId && $this->programId && $this->angkatanId,
            400,
            'Konteks Prodi, Program, dan Angkatan wajib dipilih sebelum generate kelas.'
        );

        $this->jumlahBelumBerkelas = Mahasiswa::query()
            ->belumBerkelas()
            ->where('prodi_id', $this->prodiId)
            ->where('program_id', $this->programId)
            ->where('angkatan_id', $this->angkatanId)
            ->count();

        $this->form->fill([
            'prefix_nama_kelas' => $this->saranPrefix(),
            'mode' => 'kapasitas',
            'kapasitas_maksimal' => 40,
            'jumlah_kelas' => null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Input')
                        ->schema([
                            Placeholder::make('info_mahasiswa')
                                ->label('Mahasiswa Belum Memiliki Kelas')
                                ->content(fn() => "{$this->jumlahBelumBerkelas} mahasiswa"),

                            TextInput::make('prefix_nama_kelas')
                                ->label('Awalan Nama Kelas')
                                ->helperText('Contoh: TI-2026 akan menghasilkan TI-2026-A, TI-2026-B, dst.')
                                ->required(),

                            Radio::make('mode')
                                ->label('Metode Generate')
                                ->options([
                                    'kapasitas' => 'Berdasarkan Kapasitas Maksimal per Kelas',
                                    'jumlah' => 'Berdasarkan Jumlah Kelas',
                                ])
                                ->live()
                                ->required(),

                            TextInput::make('kapasitas_maksimal')
                                ->label('Kapasitas Maksimal per Kelas')
                                ->numeric()
                                ->minValue(1)
                                ->visible(fn(Get $get) => $get('mode') === 'kapasitas')
                                ->required(fn(Get $get) => $get('mode') === 'kapasitas'),

                            TextInput::make('jumlah_kelas')
                                ->label('Jumlah Kelas')
                                ->numeric()
                                ->minValue(1)
                                ->visible(fn(Get $get) => $get('mode') === 'jumlah')
                                ->required(fn(Get $get) => $get('mode') === 'jumlah'),
                        ]),

                    Step::make('Preview')
                        ->schema([
                            Placeholder::make('preview')
                                ->label('')
                                ->content(fn(Get $get) => $this->renderPreview($get)),
                        ]),

                    Step::make('Konfirmasi')
                        ->schema([
                            Placeholder::make('ringkasan')
                                ->label('')
                                ->content(fn(Get $get) => $this->renderRingkasan($get)),
                        ]),
                ])
                    ->skippable(false)
                    ->submitAction(new HtmlString(
                        Blade::render(<<<'BLADE'
                            <x-filament::button
                                wire:click="konfirmasi"
                                wire:loading.attr="disabled"
                                wire:target="konfirmasi"
                            >
                                Buat Kelas
                            </x-filament::button>
                        BLADE)
                    )),
            ])
            ->statePath('data');
    }

    /**
     * Konfirmasi final: hitung ulang preview dari state form saat ini (jangan
     * percaya nilai yang mungkin sudah usang), lalu serahkan sepenuhnya ke
     * KelasGenerationService::simpan(). Tidak ada penulisan data lain di sini.
     */
    public function konfirmasi(KelasGenerationService $service): void
    {
        $state = $this->form->getState();
        $preview = $this->hitungPreview($state, $service);

        if ($preview->isEmpty()) {
            Notification::make()
                ->title('Tidak Ada Kelas Dibuat')
                ->body('Tidak ada mahasiswa belum berkelas dalam konteks ini, atau input belum valid.')
                ->warning()
                ->send();

            return;
        }

        $dibuat = $service->simpan($preview, $this->prodiId, $this->programId, $this->angkatanId);

        Notification::make()
            ->title('Berhasil')
            ->body($dibuat->count() . ' kelas berhasil dibuat. Lanjutkan ke Plotting Mahasiswa untuk mengisi kelas ini.')
            ->success()
            ->send();

        $this->redirect(KelasResource::getUrl('list', array_filter([
            'prodi_id' => $this->prodiId,
            'program_id' => $this->programId,
            'angkatan_id' => $this->angkatanId,
        ])));
    }

    protected function hitungPreview(array $state, KelasGenerationService $service)
    {
        return $service->hitungPreview(
            $this->jumlahBelumBerkelas,
            isset($state['jumlah_kelas']) ? (int) $state['jumlah_kelas'] : null,
            isset($state['kapasitas_maksimal']) ? (int) $state['kapasitas_maksimal'] : null,
            (string) ($state['prefix_nama_kelas'] ?? 'KELAS')
        );
    }

    protected function renderPreview(Get $get): HtmlString
    {
        $preview = $this->hitungPreview([
            'mode' => $get('mode'),
            'kapasitas_maksimal' => $get('kapasitas_maksimal'),
            'jumlah_kelas' => $get('jumlah_kelas'),
            'prefix_nama_kelas' => $get('prefix_nama_kelas'),
        ], app(KelasGenerationService::class));

        if ($preview->isEmpty()) {
            return new HtmlString('<p class="text-sm text-gray-500">Lengkapi input di langkah sebelumnya untuk melihat preview.</p>');
        }

        $rows = $preview
            ->map(fn(array $item) => "<li>{$item['nama_kelas']} — kapasitas {$item['kapasitas']}</li>")
            ->implode('');

        return new HtmlString("<ul class=\"list-disc pl-5 space-y-1 text-sm\">{$rows}</ul>");
    }

    protected function renderRingkasan(Get $get): HtmlString
    {
        $preview = $this->hitungPreview([
            'mode' => $get('mode'),
            'kapasitas_maksimal' => $get('kapasitas_maksimal'),
            'jumlah_kelas' => $get('jumlah_kelas'),
            'prefix_nama_kelas' => $get('prefix_nama_kelas'),
        ], app(KelasGenerationService::class));

        $jumlah = $preview->count();

        return new HtmlString(
            "<p class=\"text-sm\"><strong>{$jumlah} kelas</strong> akan dibuat. "
                . "Mahasiswa <strong>belum di-assign</strong> — lakukan plotting terpisah "
                . "setelah kelas ini dibuat.</p>"
        );
    }

    protected function saranPrefix(): string
    {
        $nama = RefProdi::find($this->prodiId)?->nama_prodi ?? 'KELAS';

        $inisial = collect(explode(' ', $nama))
            ->map(fn($kata) => strtoupper(substr($kata, 0, 1)))
            ->implode('');

        return $inisial . '-' . $this->angkatanId;
    }
}
