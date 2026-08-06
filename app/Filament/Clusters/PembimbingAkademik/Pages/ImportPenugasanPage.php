<?php

namespace App\Filament\Clusters\PembimbingAkademik\Pages;

use App\Exceptions\PembimbingAkademikException;
use App\Exports\PenugasanTemplateExport;
use App\Filament\Clusters\PembimbingAkademik\PembimbingAkademikCluster;
use App\Models\RefTahunAkademik;
use App\Services\PembimbingAkademikService;
use App\Services\PenugasanImportParser;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportPenugasanPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationLabel = 'Import Excel';

    protected static ?string $title = 'Import Penugasan Pembimbing (Excel)';

    protected static ?int $navigationSort = 6;
    protected string $view = 'filament.clusters.pembimbing-akademik.pages.import-penugasan-page';

    protected static ?string $cluster = PembimbingAkademikCluster::class;
    /** @var array<string, mixed> */
    public ?array $data = [];

    /** @var array<int, array<string, mixed>> */
    public array $previewRows = [];

    public bool $previewGenerated = false;

    public int $processed = 0;

    public int $totalValid = 0;

    public int $totalGagal = 0;

    public int $totalDilewati = 0;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('file')
                    ->label('File Excel (.xlsx)')
                    ->required()
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                    ])
                    ->disk('local')
                    ->directory('import-penugasan-tmp')
                    ->visibility('private')
                    ->helperText('Kolom wajib: nim_mahasiswa, nidn_dosen, jenis, tanggal_mulai, keterangan (opsional). Unduh template terlebih dahulu di atas.'),
            ])
            ->statePath('data');
    }

    public function downloadTemplate()
    {
        return Excel::download(new PenugasanTemplateExport, 'template-import-penugasan.xlsx');
    }

    public function generatePreview(): void
    {
        $state = $this->form->getState();
        $path = $state['file'] ?? null;

        if (! $path) {
            Notification::make()->title('Pilih file terlebih dahulu')->warning()->send();

            return;
        }

        $fullPath = Storage::disk('local')->path($path);
        $sheets = Excel::toArray([], $fullPath);
        $rows = $sheets[0] ?? [];

        $heading = array_map(fn($h) => strtolower(trim((string) $h)), array_shift($rows) ?? []);

        $rows = collect($rows)
            ->filter(fn($row) => collect($row)->filter(fn($cell) => $cell !== null && $cell !== '')->isNotEmpty())
            ->map(fn($row) => array_combine($heading, array_pad(array_slice($row, 0, count($heading)), count($heading), null)))
            ->all();

        $this->previewRows = app(PenugasanImportParser::class)->parse($rows)->all();
        $this->previewGenerated = true;
        $this->totalValid = collect($this->previewRows)->where('valid', true)->count();
        $this->totalGagal = collect($this->previewRows)->where('valid', false)->count();
        $this->processed = 0;
        $this->totalDilewati = 0;

        Storage::disk('local')->delete($path);
    }

    /**
     * Diproses per-batch dipanggil berulang dari JS (Alpine) sampai
     * selesai — progress bar naik secara nyata.
     */
    public function processBatch(int $batchSize = 10): array
    {
        $service = app(PembimbingAkademikService::class);
        $semesterAktifId = RefTahunAkademik::query()->where('is_active', true)->value('id');

        $validRows = collect($this->previewRows)->where('valid', true)->values();
        $slice = $validRows->slice($this->processed, $batchSize);

        foreach ($slice as $row) {
            try {
                $service->tugaskan([
                    'jenis' => $row['jenis'],
                    'mahasiswa_id' => $row['mahasiswa_id'],
                    'dosen_id' => $row['dosen_id'],
                    'semester_mulai_id' => $semesterAktifId,
                    'tanggal_mulai' => $row['tanggal_mulai'],
                    'keterangan' => $row['keterangan'],
                ]);
            } catch (PembimbingAkademikException) {
                $this->totalDilewati++;
            }

            $this->processed++;
        }

        $done = $this->processed >= $this->totalValid;

        if ($done) {
            $berhasil = $this->totalValid - $this->totalDilewati;

            Notification::make()
                ->title('Import selesai')
                ->body("{$berhasil} baris berhasil diproses, {$this->totalDilewati} dilewati (sudah ada pembimbing aktif), {$this->totalGagal} baris tidak valid diabaikan sejak awal.")
                ->success()
                ->persistent()
                ->send();
        }

        return ['done' => $done, 'processed' => $this->processed, 'total' => $this->totalValid];
    }

    public function resetImport(): void
    {
        $this->previewRows = [];
        $this->previewGenerated = false;
        $this->processed = 0;
        $this->totalValid = 0;
        $this->totalGagal = 0;
        $this->totalDilewati = 0;
        $this->form->fill();
    }
}
