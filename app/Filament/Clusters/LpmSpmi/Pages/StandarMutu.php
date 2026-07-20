<?php

namespace App\Filament\Clusters\LpmSpmi\Pages;

use App\Exports\LpmSpmi\StandarMutuExport;
use App\Filament\Clusters\LpmSpmi\LpmSpmiCluster;
use App\Services\LpmSpmi\StandarMutuService;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class StandarMutu extends Page implements HasTable
{
    use InteractsWithTable, HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected string $view = 'filament.clusters.lpm-spmi.pages.standar-mutu';

    protected static ?string $cluster = LpmSpmiCluster::class;
    protected static ?string $navigationLabel = 'Standar Mutu';

    protected static ?string $title = 'Standar Mutu';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn() => app(StandarMutuService::class)->query($this->getActiveFilters()))
            ->filters([
                SelectFilter::make('kategori')
                    ->label('Kategori')
                    ->options([
                        'AKADEMIK' => 'Akademik',
                        'NON-AKADEMIK' => 'Non-Akademik',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->columns([
                TextColumn::make('kode_standar')->label('Kode Standar')->searchable(),
                TextColumn::make('nama_standar')->label('Nama Standar')->searchable()->wrap(),
                TextColumn::make('kategori')->label('Kategori'),
                TextColumn::make('target_pencapaian')
                    ->label('Target Pencapaian')
                    ->formatStateUsing(fn($record) => "{$record->target_pencapaian}{$record->satuan}"),
                TextColumn::make('versi')->label('Versi'),
                TextColumn::make('indikators_count')->label('Jumlah Indikator')->numeric(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
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
                    new StandarMutuExport($this->getActiveFilters()),
                    'standar-mutu-' . now()->format('Ymd-His') . '.xlsx'
                )),
            Action::make('exportPdf')
                ->label('Export PDF')
                ->color('danger')
                ->icon('heroicon-o-document-arrow-down')
                ->action(fn() => $this->downloadPdf()),
        ];
    }

    protected function downloadPdf()
    {
        $rows = app(StandarMutuService::class)->exportRows($this->getActiveFilters());

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.lpm-spmi.standar-mutu', [
            'rows' => $rows,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'standar-mutu-' . now()->format('Ymd-His') . '.pdf'
        );
    }

    protected function getActiveFilters(): array
    {
        $state = $this->tableFilters ?? [];

        return [
            'kategori' => $state['kategori']['value'] ?? null,
            'is_active' => $state['is_active']['value'] ?? null,
        ];
    }
}
