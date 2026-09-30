<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\DataTransferObjects\Absensi\AbsensiDocumentData;
use App\Domain\Authorization\Services\FormResolver;
use App\Enums\NavigationGroup;
use App\Exports\Absensi\AbsensiDocumentExport;
use App\Models\RefKampus;
use App\Models\RefTahunAkademik;
use App\Services\Absensi\AbsensiDocumentService;
use App\Services\Pdf\PdfTemplateEngine;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Maatwebsite\Excel\Facades\Excel;
use UnitEnum;

class PusatAbsensi extends Page implements HasForms
{
    use HasPageShield;
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Pusat Absensi';

    protected static ?string $title = 'Pusat Absensi';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::AKADEMIK->value;

    protected static ?int $navigationSort = 40;

    protected string $view = 'filament.pages.pusat-absensi';

    /** @var array<string, mixed> */
    public array $data = [];

    public bool $previewGenerated = false;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pilih Sumber Dokumen')
                    ->description('Pilih jenis dokumen, tahun akademik, jadwal, lalu pratinjau sebelum mengunduh PDF atau Excel.')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2, 'lg' => 3])
                            ->schema([
                                Select::make('mode')
                                    ->label('Jenis Dokumen')
                                    ->options([
                                        AbsensiDocumentService::MODE_MANUAL => 'Absensi Manual',
                                        AbsensiDocumentService::MODE_ONLINE => 'Hasil Absensi Online',
                                        AbsensiDocumentService::MODE_TEMPLATE => 'Template Absensi',
                                    ])
                                    ->default(AbsensiDocumentService::MODE_MANUAL)
                                    ->live()
                                    ->native(false)
                                    ->required()
                                    ->afterStateUpdated(fn() => $this->resetPilihanDepan('mode')),

                                Select::make('tahun_akademik_id')
                                    ->label('Tahun Akademik')
                                    ->options(fn() => RefTahunAkademik::query()->orderByDesc('id')->pluck('nama_tahun', 'id'))
                                    ->default(fn() => RefTahunAkademik::query()->where('is_active', true)->value('id'))
                                    ->searchable()
                                    ->live()
                                    ->native(false)
                                    ->required()
                                    ->afterStateUpdated(fn() => $this->resetPilihanDepan('tahun_akademik_id')),

                                Select::make('prodi_id')
                                    ->label('Program Studi')
                                    ->options(fn() => app(FormResolver::class)->prodiOptions(auth()->user()))
                                    ->searchable()
                                    ->live()
                                    ->native(false)
                                    ->afterStateUpdated(fn() => $this->resetPilihanDepan('prodi_id')),

                                Select::make('kampus_id')
                                    ->label('Kampus')
                                    ->options(fn() => RefKampus::query()->orderBy('nama_kampus')->pluck('nama_kampus', 'id'))
                                    ->searchable()
                                    ->live()
                                    ->native(false)
                                    ->afterStateUpdated(fn() => $this->resetPilihanDepan('kampus_id')),

                                Select::make('kelas_id')
                                    ->label('Kelas')
                                    ->options(fn() => app(AbsensiDocumentService::class)->kelasOptions(
                                        $this->nullableId('prodi_id'),
                                        $this->nullableId('kampus_id'),
                                        auth()->user(),
                                    ))
                                    ->placeholder('Semua kelas')
                                    ->searchable()
                                    ->live()
                                    ->native(false)
                                    ->afterStateUpdated(fn() => $this->resetPilihanDepan('kelas_id')),

                                Select::make('jadwal_kuliah_id')
                                    ->label('Mata Kuliah / Jadwal')
                                    ->options(function (): array {
                                        $tahunAkademikId = (int) ($this->data['tahun_akademik_id'] ?? 0);
                                        if ($tahunAkademikId < 1) {
                                            return [];
                                        }

                                        return app(AbsensiDocumentService::class)
                                            ->scheduleOptions(
                                                $tahunAkademikId,
                                                $this->nullableId('prodi_id'),
                                                $this->nullableId('kampus_id'),
                                                $this->nullableId('kelas_id'),
                                                auth()->user(),
                                            )
                                            ->mapWithKeys(function ($jadwal) {
                                                $code = trim($jadwal->mataKuliah?->kode_mk, ' ');
                                                $label = trim($code . ' - ' . $jadwal->mataKuliah?->nama_mk . ' [' . ($jadwal->kelas?->nama_kelas ?? '-') . '] ' . $jadwal->hari . ' ' . $jadwal->jam_mulai . '-' . $jadwal->jam_selesai, ' -[]');

                                                return [$jadwal->id => $label];
                                            })->all();
                                    })
                                    ->searchable()
                                    ->live()
                                    ->native(false)
                                    ->required()
                                    ->afterStateUpdated(fn() => $this->resetPilihanDepan('jadwal_kuliah_id')),

                                Select::make('perkuliahan_sesi_id')
                                    ->label('Pertemuan')
                                    ->options(function (): array {
                                        $jadwalId = (string) ($this->data['jadwal_kuliah_id'] ?? '');
                                        if ($jadwalId === '') {
                                            return [];
                                        }

                                        return app(AbsensiDocumentService::class)
                                            ->sessionOptions($jadwalId)
                                            ->mapWithKeys(fn($sesi) => [
                                                $sesi->id => 'Pertemuan ' . $sesi->pertemuan_ke
                                                    . ' — ' . optional($sesi->waktu_mulai_rencana)->format('d/m/Y'),
                                            ])->all();
                                    })
                                    ->visible(fn(): bool => ($this->data['mode'] ?? '') === AbsensiDocumentService::MODE_ONLINE)
                                    ->searchable()
                                    ->live()
                                    ->native(false)
                                    ->required(fn(): bool => ($this->data['mode'] ?? '') === AbsensiDocumentService::MODE_ONLINE)
                                    ->afterStateUpdated(fn() => $this->resetPilihanDepan('perkuliahan_sesi_id')),

                                DatePicker::make('tanggal')
                                    ->label('Tanggal')
                                    ->visible(fn(): bool => ($this->data['mode'] ?? '') !== AbsensiDocumentService::MODE_ONLINE)
                                    ->live()
                                    ->afterStateUpdated(fn() => $this->resetPreview())
                                    ->helperText('Kosongkan untuk memakai tanggal rencana pada sesi/periode akademik.'),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function preview(): void
    {
        try {
            $this->form->getState();
            $this->previewGenerated = true;
        } catch (\Throwable $exception) {
            $this->previewGenerated = false;
            Notification::make()
                ->danger()
                ->title('Dokumen tidak dapat dibuat')
                ->body($exception->getMessage())
                ->send();
        }
    }

    /** Urutan filter berjenjang: field setelah parent di-reset agar tidak bawa pilihan lama. */
    private const FIELD_ORDER = [
        'mode',
        'tahun_akademik_id',
        'prodi_id',
        'kampus_id',
        'kelas_id',
        'jadwal_kuliah_id',
        'perkuliahan_sesi_id',
        'tanggal',
    ];

    /**
     * Reset preview + select turunan setelah sebuah filter induk berubah,
     * supaya pilihan lama (mis. jadwal dari prodi sebelumnya) tidak ikut terkirim.
     */
    public function resetPilihanDepan(string $changedField): void
    {
        $index = array_search($changedField, self::FIELD_ORDER, true);

        if ($index === false) {
            return;
        }

        foreach (array_slice(self::FIELD_ORDER, $index + 1) as $field) {
            // Tahun akademik & mode punya default; jangan dikosongkan total.
            if (in_array($field, ['mode', 'tahun_akademik_id'], true)) {
                continue;
            }

            $this->data[$field] = null;
        }

        $this->previewGenerated = false;
    }

    public function resetPreview(): void
    {
        $this->previewGenerated = false;
    }

    public function downloadPdf()
    {
        $document = $this->resolveValidated();

        $pdf = app(PdfTemplateEngine::class)
            ->render('pdf.absensi.document', [
                'mode' => $document->mode,
                'akademik' => $document->akademik,
                'rows' => $document->rows,
                'summary' => $document->summary,
                'pertemuan' => $document->pertemuan,
            ], [
                // Base layout PDF memakai @page A4 landscape (kop surat + tabel lebar),
                // jadi orientation harus landscape agar konsisten dengan CSS-nya.
                'paper' => 'a4',
                'orientation' => 'landscape',
            ]);

        return response()->streamDownload(
            fn() => print($pdf->output()),
            $this->filename('pdf'),
            ['Content-Type' => 'application/pdf'],
        );
    }

    public function downloadExcel()
    {
        return Excel::download(
            new AbsensiDocumentExport($this->resolveValidated()),
            $this->filename('xlsx'),
        );
    }

    /**
     * Validasi form dulu lalu resolve — memastikan PDF/Excel memakai
     * filter yang sama persis dengan yang terlihat di UI (state ter-sinkron).
     */
    private function resolveValidated(): AbsensiDocumentData
    {
        $this->form->getState();

        return $this->document();
    }

    public function document(): AbsensiDocumentData
    {
        return app(AbsensiDocumentService::class)->resolve(
            (string) ($this->data['mode'] ?? AbsensiDocumentService::MODE_MANUAL),
            $this->requiredId('tahun_akademik_id'),
            (string) ($this->data['jadwal_kuliah_id'] ?? ''),
            filled($this->data['perkuliahan_sesi_id'] ?? null) ? (string) $this->data['perkuliahan_sesi_id'] : null,
            filled($this->data['tanggal'] ?? null) ? (string) $this->data['tanggal'] : null,
            auth()->user(),
        );
    }

    #[Computed]
    public function documentSummary(): ?array
    {
        if (! $this->previewGenerated) {
            return null;
        }

        try {
            $document = $this->resolveValidated();
        } catch (\Throwable) {
            return null;
        }

        return [
            'mode' => match ($document->mode) {
                AbsensiDocumentService::MODE_ONLINE => 'Hasil Absensi Online',
                AbsensiDocumentService::MODE_TEMPLATE => 'Template Absensi',
                default => 'Absensi Manual',
            },
            'akademik' => $document->akademik,
            'summary' => $document->summary,
            'rows' => array_slice($document->rows, 0, 20),
        ];
    }

    private function nullableId(string $field): ?int
    {
        $value = $this->data[$field] ?? null;

        return filled($value) ? (int) $value : null;
    }

    private function requiredId(string $field): int
    {
        $value = $this->nullableId($field);

        if ($value === null) {
            throw ValidationException::withMessages([$field => 'Wajib dipilih.']);
        }

        return $value;
    }

    private function filename(string $extension): string
    {
        $document = $this->resolveValidated();

        return Str::ascii(sprintf(
            'absensi-%s-%s-%s.%s',
            $document->mode,
            Str::slug($document->akademik['mata_kuliah'] ?: 'mata-kuliah'),
            now()->format('Ymd-His'),
            $extension,
        ));
    }
}
