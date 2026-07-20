<?php

namespace App\Filament\Clusters\LaporanAkademik\Pages;

use App\Filament\Clusters\LaporanAkademik\LaporanAkademikCluster;
use App\Filament\Traits\HasLaporanFilters;
use App\Models\Mahasiswa;
use App\Services\Laporan\TranskripAkademikService;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class TranskripAkademikPage extends Page
{
    use HasLaporanFilters;
    protected string $view = 'filament.clusters.laporan-akademik.pages.transkrip-akademik-page';
    protected static ?string $cluster = LaporanAkademikCluster::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Transkrip Akademik';
    protected static ?string $title = 'Laporan Transkrip Akademik';
    protected static ?string $slug = 'laporan/akademik/transkrip';
    protected static ?int $navigationSort = 3;


    public ?string $mahasiswaId = null;

    public function schema(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('mahasiswaId')
                ->label('Pilih Mahasiswa')
                ->searchable()
                ->native(false)
                ->required()
                ->getSearchResultsUsing(function (string $search) {
                    return Mahasiswa::query()
                        ->with('person')
                        ->where('nim', 'like', "%{$search}%")
                        ->orWhereHas('person', fn($q) => $q->where('nama_lengkap', 'like', "%{$search}%"))
                        ->limit(20)
                        ->get()
                        ->mapWithKeys(fn($m) => [$m->id => "{$m->nim} - {$m->person->nama_lengkap}"])
                        ->toArray();
                })
                ->getOptionLabelUsing(function ($value) {
                    $m = Mahasiswa::with('person')->find($value);
                    return $m ? "{$m->nim} - {$m->person->nama_lengkap}" : null;
                })
                ->live(),
        ]);
    }

    /**
     * Ambil data transkrip untuk mahasiswa yang sedang dipilih.
     */
    public function getTranskripData(): ?array
    {
        if (empty($this->mahasiswaId)) {
            return null;
        }

        $result = app(TranskripAkademikService::class)->getData([
            'mahasiswa_id' => $this->mahasiswaId,
        ]);

        return $result;
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->visible(fn() => filled($this->mahasiswaId))
                ->action('exportPdf'),
        ];
    }

    public function exportPdf()
    {
        $pdfData = app(TranskripAkademikService::class)->getDataForPdf($this->mahasiswaId);

        $pdf = Pdf::loadView('exports.laporan.transkrip-akademik-pdf', $pdfData)
            ->setPaper('a4', 'portrait');

        $nim = $pdfData['mahasiswa']['nim'] ?? 'mahasiswa';

        return response()->streamDownload(
            fn() => print($pdf->output()),
            "Transkrip_Akademik_{$nim}_" . now()->format('Y-m-d') . '.pdf'
        );
    }
}
