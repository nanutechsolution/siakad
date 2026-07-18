<?php

namespace App\Filament\Pages;

use App\Domain\Authorization\Services\FormResolver;
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
    use HasPageShield;

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
                        'xl' => 3,
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

                        // Group 2: Organisasi. Options SEKARANG discope ke
                        // accessibleFakultasIds()/accessibleProdiIds() user lewat
                        // FormResolver -- sebelumnya RefFakultas::query()->pluck(...)
                        // menampilkan SEMUA fakultas/prodi ke siapa pun, termasuk yang
                        // di luar hak akses mereka.
                        Group::make([
                            Select::make('fakultas_id')
                                ->label('Fakultas')
                                ->options(fn() => app(FormResolver::class)->fakultasOptions(auth()->user()))
                                ->live()
                                ->searchable()
                                ->afterStateUpdated(fn($set) => $set('prodi_id', null)),
                            Select::make('prodi_id')
                                ->label('Program Studi')
                                ->options(fn(callable $get) => app(FormResolver::class)
                                    ->prodiOptionsForFakultas(auth()->user(), $get('fakultas_id')))
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
