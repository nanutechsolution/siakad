<?php

namespace App\Filament\Clusters\Migration\Pages;

use App\Application\Migration\Jobs\ProcessGradeMigrationBatchJob;
use App\Application\Migration\Services\MigrationCancellationService;
use App\Application\Migration\Services\MigrationSourceFactory;
use App\Domain\Migration\Enums\MigrationBatchStatus;
use App\Domain\Migration\Enums\MigrationSource;
use App\Filament\Clusters\Migration\MigrationCluster;
use App\Models\MigrationBatch;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class GradeMigration extends Page
{
    use HasPageShield;
    protected string $view = 'filament.clusters.migration.pages.grade-migration';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationLabel = 'Migrasi Nilai';
    protected static ?int $navigationSort = 2;
    protected static ?string $cluster = MigrationCluster::class;
    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public ?int $migrationBatchId = null;

    /** @var array<string, mixed>|null */
    public ?array $previewData = null;

    public function mount(): void
    {
        $this->form->fill([
            'source' => MigrationSource::EXCEL->value,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Pilih Sumber')
                        ->description('Tentukan asal data migrasi')
                        ->schema([
                            Radio::make('source')
                                ->label('Sumber Data')
                                ->options(collect(MigrationSource::cases())
                                    ->mapWithKeys(fn(MigrationSource $case): array => [$case->value => $case->label()])
                                    ->toArray())
                                ->descriptions([
                                    MigrationSource::EXCEL->value => 'Unggah file .xlsx sesuai template kolom.',
                                    MigrationSource::CSV->value => 'Unggah file .csv sesuai template kolom.',
                                    MigrationSource::NEO_DATABASE->value => 'Belum tersedia — extension point pengembangan mendatang.',
                                    MigrationSource::NEO_API->value => 'Belum tersedia — extension point pengembangan mendatang.',
                                ])
                                ->disableOptionWhen(
                                    fn(string $value): bool => ! MigrationSource::from($value)->isImplemented()
                                )
                                ->live()
                                ->required(),
                        ]),

                    Step::make('Unggah File')
                        ->description('Unggah file sesuai template kolom yang ditentukan')
                        ->schema([
                            FileUpload::make('file')
                                ->label('File Migrasi Nilai')
                                ->disk('local')
                                ->directory('migration-uploads')
                                ->acceptedFileTypes([
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    'application/vnd.ms-excel',
                                    'text/csv',
                                    'text/plain',
                                ])
                                ->maxSize(20 * 1024)
                                ->required(),
                        ])
                        ->afterValidation(function (): void {
                            $this->runStructuralValidation();
                        }),

                    Step::make('Pratinjau')
                        ->description('Ringkasan data sebelum diimpor')
                        ->schema([
                            ViewField::make('preview')
                                ->view('filament.clusters.migration.partials.preview')
                                ->viewData(fn(): array => ['preview' => $this->previewData]),
                        ]),

                    Step::make('Validasi')
                        ->description('Detail baris yang gagal validasi')
                        ->schema([
                            ViewField::make('validation')
                                ->view('filament.clusters.migration.partials.validation')
                                ->viewData(fn(): array => ['preview' => $this->previewData]),
                        ]),

                    Step::make('Impor')
                        ->description('Jalankan proses migrasi')
                        ->schema([
                            ViewField::make('import')
                                ->view('filament.clusters.migration.partials.import')
                                ->viewData(fn(): array => [
                                    'batch' => $this->getBatchProgress(),
                                ]),
                        ]),

                    Step::make('Hasil')
                        ->description('Ringkasan hasil migrasi')
                        ->schema([
                            ViewField::make('result')
                                ->view('filament.clusters.migration.partials.result')
                                ->viewData(fn(): array => [
                                    'batch' => $this->getBatchProgress(),
                                ]),
                        ]),
                ])
                    ->persistStepInQueryString()
                    ->submitAction(new HtmlString('')),
            ])
            ->statePath('data');
    }

    private function runStructuralValidation(): void
    {
        $state = $this->form->getState();
        $source = MigrationSource::from($state['source']);

        if (! $source->isImplemented()) {
            Notification::make()
                ->title('Sumber belum tersedia')
                ->body($source->label() . ' belum diimplementasikan.')
                ->danger()
                ->send();

            throw new Halt();
        }

        $filePath = Storage::disk('local')->path($state['file']);

        $migrationSource = app(MigrationSourceFactory::class)->make($source, ['file_path' => $filePath]);

        $structuralErrors = $migrationSource->validate();

        if ($structuralErrors !== []) {
            Notification::make()
                ->title('File tidak valid')
                ->body(implode("\n", $structuralErrors))
                ->danger()
                ->send();

            throw new Halt();
        }

        $preview = $migrationSource->preview();

        $this->previewData = [
            'total_records' => $preview->totalRecords,
            'valid_count' => $preview->validCount,
            'invalid_count' => $preview->invalidCount,
            'invalid_rows' => $preview->invalidRows,
            'warnings' => $preview->warnings,
        ];

        $this->migrationBatchId = MigrationBatch::query()->create([
            'source' => $source,
            'status' => MigrationBatchStatus::PROCESSING,
            'file_name' => basename($filePath),
            'file_path' => $filePath,
            'parameter_snapshot' => ['file_path' => $filePath],
            'total_rows' => $preview->totalRecords,
            'created_by' => auth()->id(),
        ])->id;
    }

    public function startImport(): void
    {
        if ($this->migrationBatchId === null) {
            return;
        }

        ProcessGradeMigrationBatchJob::dispatch($this->migrationBatchId);

        Notification::make()
            ->title('Proses migrasi dimulai')
            ->body('Progres dapat dipantau langsung pada halaman ini.')
            ->success()
            ->send();
    }

    public function cancelImport(): void
    {
        if ($this->migrationBatchId === null) {
            return;
        }

        app(MigrationCancellationService::class)->requestCancel($this->migrationBatchId);

        Notification::make()
            ->title('Permintaan pembatalan dikirim')
            ->body('Proses akan berhenti setelah baris yang sedang berjalan selesai.')
            ->warning()
            ->send();
    }

    public function getBatchProgress(): ?MigrationBatch
    {
        return $this->migrationBatchId
            ? MigrationBatch::query()->find($this->migrationBatchId)
            : null;
    }
}
