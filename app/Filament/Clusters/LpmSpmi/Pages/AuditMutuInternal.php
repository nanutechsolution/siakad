<?php

namespace App\Filament\Clusters\LpmSpmi\Pages;

use App\Exports\LpmSpmi\AuditMutuInternalExport;
use App\Filament\Clusters\LpmSpmi\LpmSpmiCluster;
use App\Filament\Widgets\LpmSpmi\AuditMutuInternalStatsOverview;
use App\Models\LpmAmiPeriode;
use App\Models\RefProdi;
use App\Services\LpmSpmi\AuditMutuInternalService;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class AuditMutuInternal extends Page implements HasTable
{
    use InteractsWithTable, HasPageShield;
    protected string $view = 'filament.clusters.lpm-spmi.pages.audit-mutu-internal';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass-circle';
    protected static ?string $navigationLabel = 'Audit Mutu Internal';
    protected static ?string $title = 'Audit Mutu Internal (AMI)';

    protected static ?string $cluster = LpmSpmiCluster::class;

    protected function getHeaderWidgets(): array
    {
        return [AuditMutuInternalStatsOverview::class];
    }

    protected function getHeaderWidgetsData(): array
    {
        return ['filters' => $this->getActiveFilters()];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn() => app(AuditMutuInternalService::class)->query($this->getActiveFilters()))
            ->filters([
                SelectFilter::make('periode_id')
                    ->label('Periode Audit')
                    ->options(fn() => LpmAmiPeriode::query()->orderByDesc('id')->pluck('nama_periode', 'id')),
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->options(fn() => RefProdi::query()->pluck('nama_prodi', 'id')),
                SelectFilter::make('klasifikasi')
                    ->label('Klasifikasi')
                    ->options([
                        'OB' => 'Observasi',
                        'KTS_MINOR' => 'KTS Minor',
                        'KTS_MAYOR' => 'KTS Mayor',
                    ]),
                SelectFilter::make('status_workflow')
                    ->label('Status Workflow')
                    ->options([
                        'OPEN' => 'Open',
                        'ACTION_PLAN' => 'Action Plan',
                        'VERIFICATION' => 'Verification',
                        'CLOSED' => 'Closed',
                    ]),
            ])
            ->columns([
                TextColumn::make('periode.nama_periode')->label('Periode')->searchable(),
                TextColumn::make('prodi.nama_prodi')->label('Prodi')->searchable(),
                TextColumn::make('standar.nama_standar')->label('Standar')->wrap(),
                TextColumn::make('klasifikasi')
                    ->label('Klasifikasi')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'KTS_MAYOR' => 'danger',
                        'KTS_MINOR' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('auditor_name')->label('Auditor'),
                TextColumn::make('status_workflow')->label('Status Workflow')->badge(),
                TextColumn::make('deadline_perbaikan')->label('Deadline Perbaikan')->date('d/m/Y'),
                IconColumn::make('is_closed')->label('Closed')->boolean(),
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
                    new AuditMutuInternalExport($this->getActiveFilters()),
                    'audit-mutu-internal-' . now()->format('Ymd-His') . '.xlsx'
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
        $rows = app(AuditMutuInternalService::class)->exportRows($this->getActiveFilters());
        $summary = app(AuditMutuInternalService::class)->summary($this->getActiveFilters());

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.lpm-spmi.audit-mutu-internal', [
            'rows' => $rows,
            'summary' => $summary,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'audit-mutu-internal-' . now()->format('Ymd-His') . '.pdf'
        );
    }

    protected function getActiveFilters(): array
    {
        $state = $this->tableFilters ?? [];

        return [
            'periode_id' => $state['periode_id']['value'] ?? null,
            'prodi_id' => $state['prodi_id']['value'] ?? null,
            'klasifikasi' => $state['klasifikasi']['value'] ?? null,
            'status_workflow' => $state['status_workflow']['value'] ?? null,
        ];
    }
}
