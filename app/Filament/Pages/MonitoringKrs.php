<?php

namespace App\Filament\Pages;

use App\Domain\Authorization\Services\DataVisibilityResolver;
use App\Enums\NavigationGroup;
use App\Filament\Widgets\MonitoringKrs\KrsApprovalPieChart;
use App\Filament\Widgets\MonitoringKrs\KrsProgressPerProdiChart;
use App\Filament\Widgets\MonitoringKrs\KrsStatsOverview;
use App\Filament\Widgets\MonitoringKrs\KrsTrendLineChart;
use App\Filament\Widgets\MonitoringKrs\KrsWarningPanel;
use App\Filament\Widgets\MonitoringKrs\MonitoringKrsTable;
use App\Models\RefAngkatan;
use App\Models\RefTahunAkademik;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

class MonitoringKrs extends Page
{
    use HasFiltersForm;
    protected string $view = 'filament.pages.monitoring-krs';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::AKADEMIK->value;
    protected static ?string $navigationLabel = 'Monitoring KRS';
    protected static ?string $title = 'Monitoring KRS';
    protected static ?int $navigationSort = 10;
    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Filter Monitoring')
                ->description('Gunakan filter di bawah untuk menyaring data KRS secara spesifik.')
                ->icon('heroicon-o-funnel')
                ->collapsible()
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3, // Menggunakan 3 kolom agar lebih luas dan tidak sesak
                    ])->schema([
                        Group::make([
                            Select::make('tahun_akademik_id')
                                ->label('Tahun Akademik')
                                ->options(RefTahunAkademik::query()->orderByDesc('id')->pluck('nama_tahun', 'id'))
                                ->default(RefTahunAkademik::query()->where('is_active', true)->value('id'))
                                ->live(),
                            Select::make('semester')
                                ->label('Semester')
                                ->options([1 => 'Ganjil', 2 => 'Genap', 3 => 'Pendek'])
                                ->live(),
                        ]),

                        // Group 2: Organisasi
                        Group::make([
                            Select::make('fakultas_id')
                                ->label('Fakultas')
                                ->options(\App\Models\RefFakultas::query()->pluck('nama_fakultas', 'id')) // Pastikan model benar
                                ->live()
                                ->searchable(),
                            Select::make('prodi_id')
                                ->label('Program Studi')
                                ->options(function (callable $get) {
                                    $fakultasId = $get('fakultas_id');
                                    $query = \App\Models\RefProdi::query();
                                    if ($fakultasId) {
                                        $query->where('fakultas_id', $fakultasId);
                                    }

                                    return $query->pluck('nama_prodi', 'id');
                                })
                                ->live()
                                ->searchable(),
                        ]),
                        // Group 3: Atribut Mahasiswa
                        Group::make([
                            Select::make('angkatan_id')
                                ->label('Angkatan')
                                ->options(RefAngkatan::query()->orderByDesc('id_tahun')->pluck('id_tahun', 'id_tahun'))
                                ->live(),
                            Select::make('status_krs')
                                ->label('Status KRS')
                                ->options([
                                    'BELUM_KRS' => 'Belum KRS',
                                    'DRAFT' => 'Draft',
                                    'DIAJUKAN' => 'Diajukan',
                                    'DISETUJUI' => 'Disetujui',
                                    'DITOLAK' => 'Ditolak',
                                ])
                                ->placeholder('Semua Status')
                                ->live(),
                        ]),
                    ]),
                ])->columnSpanFull()
                ->compact()
                ->headerActions([
                    Action::make('reset')
                        ->label('Reset Filter')
                        ->color('danger')
                        ->icon('heroicon-o-trash')
                        ->action(fn($livewire) => $livewire->resetFilter()),
                ]),
        ]);
    }

    public function getWidgets(): array
    {
        return [
            KrsStatsOverview::class,
            KrsProgressPerProdiChart::class,
            KrsApprovalPieChart::class,
            KrsTrendLineChart::class,
            KrsWarningPanel::class,
            MonitoringKrsTable::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 1;
    }
}
