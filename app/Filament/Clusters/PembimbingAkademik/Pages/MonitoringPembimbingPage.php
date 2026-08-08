<?php

namespace App\Filament\Clusters\PembimbingAkademik\Pages;

use App\Enums\PembimbingAkademikJenis;
use App\Enums\PembimbingAkademikStatus;
use App\Exports\MahasiswaTanpaWaliExport;
use App\Filament\Clusters\PembimbingAkademik\PembimbingAkademikCluster;
use App\Filament\Widgets\PembimbingStatsWidget;
use App\Models\Mahasiswa;
use App\Models\RefProdi;
use App\Services\PembimbingAkademikService;
use App\Support\Utf8;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringPembimbingPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.clusters.pembimbing-akademik.pages.monitoring-pembimbing-page';
    protected static ?string $navigationLabel = 'Monitoring Pembimbing Akademik';
    protected static ?string $modelLabel = 'Monitoring Pembimbing Akademik';
    protected static ?string $clusterBreadcrumb = 'Monitoring Pembimbing Akademik';
    protected static ?int $navigationSort = 4;
    protected static ?string $title = 'Monitoring Pembimbing Akademik';
    protected static ?string $description = 'Halaman ini digunakan untuk memantau kinerja dan aktivitas pembimbing akademik. Anda dapat melihat daftar pembimbing, jumlah mahasiswa yang dibimbing, dan informasi terkait lainnya. Halaman ini membantu dalam mengevaluasi efektivitas pembimbing akademik dan memastikan kualitas bimbingan yang diberikan kepada mahasiswa.';
    protected static ?string $slug = 'monitoring-pembimbing-akademik';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $cluster = PembimbingAkademikCluster::class;
   protected function getHeaderWidgets(): array
    {
        return [
            PembimbingStatsWidget::class,
        ];
    }
 
    protected function service(): PembimbingAkademikService
    {
        return app(PembimbingAkademikService::class);
    }
 
    /**
     * @return Collection<int, array{dosen: \App\Models\Dosen, total: int}>
     */
    public function getBebanDosenTerbanyak(): Collection
    {
        return $this->service()->bebanDosenTerbanyak(5);
    }
 
    public function table(Table $table): Table
    {
        return $table
            ->query($this->service()->queryMahasiswaTanpaWali())
            ->heading('Mahasiswa Tanpa Dosen Wali Aktif')
            ->columns([
                TextColumn::make('nim')->searchable()->sortable(),
                TextColumn::make('nama_mahasiswa')
                    ->label('Nama')
                    ->getStateUsing(fn ($record) => Utf8::clean($record->person?->nama_lengkap))
                    ->searchable(query: fn ($query, string $search) => $query->whereHas('person', fn ($q) => $q->where('nama_lengkap', 'like', "%{$search}%"))),
                TextColumn::make('prodi.nama_prodi')
                    ->label('Program Studi')
                    ->formatStateUsing(fn (?string $state) => $state ? Utf8::clean($state) : null)
                    ->searchable(),
                TextColumn::make('angkatan_id')
                    ->label('Angkatan')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->options(fn () => RefProdi::query()->orderBy('nama_prodi')->pluck('nama_prodi', 'id')->map(fn (?string $n) => Utf8::clean($n)))
                    ->searchable(),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(fn () => Excel::download(
                        new MahasiswaTanpaWaliExport($this->service()->queryMahasiswaTanpaWali()),
                        'mahasiswa-tanpa-wali-'.now()->format('Ymd-His').'.xlsx'
                    )),
            ])
            ->emptyStateHeading('Semua mahasiswa sudah punya Dosen Wali 🎉')
            ->emptyStateDescription('Tidak ada tindak lanjut yang diperlukan saat ini.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->defaultSort('nim');
    }
}
 

