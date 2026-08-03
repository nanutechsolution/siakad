<?php

namespace App\Filament\Clusters\PembimbingAkademik\Pages;

use App\Enums\PembimbingAkademikJenis;
use App\Enums\PembimbingAkademikStatus;
use App\Filament\Clusters\PembimbingAkademik\PembimbingAkademikCluster;
use App\Models\Mahasiswa;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
    public function getTotalMahasiswaAktif(): int
    {
        return Mahasiswa::query()->whereNull('deleted_at')->count();
    }

    public function getTotalTerbimbing(): int
    {
        return Mahasiswa::query()
            ->whereNull('deleted_at')
            ->whereHas('pembimbingAkademik', fn(Builder $q) => $q
                ->where('jenis', PembimbingAkademikJenis::DOSEN_WALI)
                ->where('status', PembimbingAkademikStatus::AKTIF))
            ->count();
    }

    public function getTotalBelumTerbimbing(): int
    {
        return $this->getTotalMahasiswaAktif() - $this->getTotalTerbimbing();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Mahasiswa::query()
                    ->whereNull('deleted_at')
                    ->whereDoesntHave('pembimbingAkademik', fn(Builder $q) => $q
                        ->where('jenis', PembimbingAkademikJenis::DOSEN_WALI)
                        ->where('status', PembimbingAkademikStatus::AKTIF))
            )
            ->heading('Mahasiswa Tanpa Dosen Wali Aktif')
            ->columns([
                TextColumn::make('nim')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('person.nama_lengkap')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('prodi.nama_prodi')
                    ->label('Program Studi')
                    ->searchable(),
                TextColumn::make('angkatan_id')
                    ->label('Angkatan')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->relationship('prodi', 'nama_prodi')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('nim');
    }
}
