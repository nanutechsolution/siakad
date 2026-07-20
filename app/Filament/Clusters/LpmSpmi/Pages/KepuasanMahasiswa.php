<?php

namespace App\Filament\Clusters\LpmSpmi\Pages;

use App\Exports\LpmSpmi\KepuasanMahasiswaExport;
use App\Filament\Clusters\LpmSpmi\LpmSpmiCluster;
use App\Models\RefTahunAkademik;
use App\Services\LpmSpmi\KepuasanMahasiswaService;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class KepuasanMahasiswa extends Page implements HasTable
{
    use InteractsWithTable, HasPageShield;

    protected string $view = 'filament.clusters.lpm-spmi.pages.kepuasan-mahasiswa';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-face-smile';
    protected static ?string $navigationLabel = 'Kepuasan Mahasiswa';
    protected static ?string $title = 'Kepuasan Mahasiswa';
    protected static ?string $cluster = LpmSpmiCluster::class;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn() => app(KepuasanMahasiswaService::class)->query($this->getActiveFilters()))
            ->filters([
                SelectFilter::make('tahun_akademik_id')
                    ->label('Tahun Akademik')
                    ->options(fn() => RefTahunAkademik::query()->orderByDesc('id')->pluck('nama_tahun', 'id'))
                    ->query(fn($query) => $query),
            ])
            ->columns([
                TextColumn::make('nama_kelompok')->label('Kelompok')->searchable(),
                TextColumn::make('bunyi_pertanyaan')->label('Pertanyaan')->wrap()->searchable(),
                TextColumn::make('jumlah_responden')->label('Jumlah Responden')->numeric(),
                TextColumn::make('rata_rata_skor')->label('Rata-rata Skor')->numeric(),
            ])
            ->searchable()
            ->paginated([10, 25, 50, 100]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-table-cells')
                ->action(fn() => Excel::download(
                    new KepuasanMahasiswaExport($this->getActiveFilters()),
                    'kepuasan-mahasiswa-' . now()->format('Ymd-His') . '.xlsx'
                )),
            Action::make('exportPdf')
            ->color('danger')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->action(fn() => $this->downloadPdf()),
        ];
    }

    protected function downloadPdf()
    {
        $rows = app(KepuasanMahasiswaService::class)->exportRows($this->getActiveFilters());

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.lpm-spmi.kepuasan-mahasiswa', [
            'rows' => $rows,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'kepuasan-mahasiswa-' . now()->format('Ymd-His') . '.pdf'
        );
    }

    protected function getActiveFilters(): array
    {
        $state = $this->tableFilters ?? [];

        return [
            'tahun_akademik_id' => $state['tahun_akademik_id']['value'] ?? null,
        ];
    }
}
