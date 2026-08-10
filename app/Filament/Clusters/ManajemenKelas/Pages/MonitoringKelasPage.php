<?php

namespace App\Filament\Clusters\ManajemenKelas\Pages;

use App\Domain\Authorization\Services\FormResolver;
use App\Filament\Clusters\ManajemenKelas\ManajemenKelasCluster;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\RefAngkatan;
use App\Models\RefProdi;
use App\Services\Kelas\ManajemenKelasService;
use App\Support\Utf8;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MonitoringKelasPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $cluster = ManajemenKelasCluster::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Monitoring Kelas';

    protected static ?string $title = 'Monitoring Kapasitas Kelas';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user());
    }
    protected static ?int $navigationSort = 4;
    protected string $view = 'filament.clusters.manajemen-kelas.pages.monitoring-kelas-page';
    protected function service(): ManajemenKelasService
    {
        return app(ManajemenKelasService::class);
    }

    public function getTotalKelas(): int
    {
        return $this->service()->totalKelas();
    }

    public function getTotalMahasiswaTanpaKelas(): int
    {
        return $this->service()->totalMahasiswaTanpaKelasAktif();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Mahasiswa::query()->whereNull('deleted_at')->whereDoesntHave('mahasiswaKelas', fn(Builder $q) => $q->whereNull('tanggal_keluar')))
            ->heading('Mahasiswa Belum Punya Kelas')
            ->columns([
                TextColumn::make('nim')->searchable()->sortable(),
                TextColumn::make('nama')
                    ->label('Nama')
                    ->getStateUsing(fn(Mahasiswa $record) => Utf8::clean($record->person?->nama_lengkap))
                    ->searchable(query: fn(Builder $query, string $search) => $query->whereHas('person', fn($q) => $q->where('nama_lengkap', 'like', "%{$search}%"))),
                TextColumn::make('prodi.nama_prodi')
                    ->label('Program Studi')
                    ->formatStateUsing(fn(?string $state) => $state ? Utf8::clean($state) : null),
                TextColumn::make('angkatan_id')
                    ->label('Angkatan')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->options(fn() => app(FormResolver::class)->prodiOptions(auth()->user())),
                SelectFilter::make('angkatan_id')
                    ->label('Angkatan')
                    ->options(fn() => RefAngkatan::query()->orderByDesc('id_tahun')->pluck('id_tahun', 'id_tahun')),
            ])
            ->emptyStateHeading('Semua mahasiswa sudah punya kelas 🎉')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->defaultSort('nim');
    }

    /**
     * Daftar kelas beserta status kapasitasnya, dipakai blade untuk
     * panel visual "Kapasitas per Kelas".
     */
    public function getKapasitasKelas()
    {
        return Kelas::query()
            ->orderByDesc('angkatan_id')
            ->orderBy('nama_kelas')
            ->limit(50)
            ->get()
            ->map(function (Kelas $kelas) {
                $jumlah = $this->service()->jumlahAnggotaAktif($kelas->id);
                $sisa = $this->service()->kapasitasTersisa($kelas);

                return [
                    'nama' => Utf8::clean($kelas->nama_kelas),
                    'jumlah' => $jumlah,
                    'kapasitas' => $kelas->kapasitas,
                    'persen' => $kelas->kapasitas ? min(100, round(($jumlah / max(1, $kelas->kapasitas)) * 100)) : null,
                    'penuh' => $sisa === 0,
                ];
            });
    }
}
