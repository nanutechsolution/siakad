<?php

namespace App\Filament\Clusters\LpmSpmi\Pages;

use App\Exports\LpmSpmi\CapaianPembelajaranExport;
use App\Filament\Clusters\LpmSpmi\LpmSpmiCluster;
use App\Models\RefProdi;
use App\Services\LpmSpmi\CapaianPembelajaranService;
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

class CapaianPembelajaran extends Page implements HasTable
{
    use InteractsWithTable, HasPageShield;

    protected string $view = 'filament.clusters.lpm-spmi.pages.capaian-pembelajaran';

    protected static ?string $cluster = LpmSpmiCluster::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Capaian Pembelajaran';

    protected static ?string $title = 'Capaian Pembelajaran';


    public function table(Table $table): Table
    {
        return $table
            ->query(fn() => app(CapaianPembelajaranService::class)->query($this->getActiveFilters()))
            ->filters([
                SelectFilter::make('tahun')
                    ->label('Tahun')
                    ->options(fn() => \App\Models\LpmIkuTarget::query()
                        ->distinct()
                        ->orderByDesc('tahun')
                        ->pluck('tahun', 'tahun')),
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->options(fn() => RefProdi::query()->pluck('nama_prodi', 'id')),
            ])
            ->columns([
                TextColumn::make('indikator.kode_indikator')->label('Kode Indikator')->searchable(),
                TextColumn::make('indikator.nama_indikator')->label('Nama Indikator')->searchable(),
                TextColumn::make('prodi.nama_prodi')->label('Prodi')->default('Institusi'),
                TextColumn::make('tahun')->label('Tahun')->sortable(),
                TextColumn::make('target_nilai')->label('Target')->numeric(),
                TextColumn::make('capaian_nilai')->label('Capaian')->numeric(),
                TextColumn::make('persen_capaian')
                    ->label('% Capaian')
                    ->state(fn($record) => CapaianPembelajaranService::persenCapaian($record) . '%'),
                TextColumn::make('status')
                    ->label('Status')
                    ->state(fn($record) => CapaianPembelajaranService::status(CapaianPembelajaranService::persenCapaian($record)))
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'Tercapai' => 'success',
                        'Mendekati Target' => 'warning',
                        default => 'danger',
                    }),
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
                    new CapaianPembelajaranExport($this->getActiveFilters()),
                    'capaian-pembelajaran-' . now()->format('Ymd-His') . '.xlsx'
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
        $rows = app(CapaianPembelajaranService::class)->exportRows($this->getActiveFilters());

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.lpm-spmi.capaian-pembelajaran', [
            'rows' => $rows,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'capaian-pembelajaran-' . now()->format('Ymd-His') . '.pdf'
        );
    }

    protected function getActiveFilters(): array
    {
        $state = $this->tableFilters ?? [];

        return [
            'tahun' => $state['tahun']['value'] ?? null,
            'prodi_id' => $state['prodi_id']['value'] ?? null,
        ];
    }
}
