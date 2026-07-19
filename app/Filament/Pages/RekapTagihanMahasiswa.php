<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\Laporan\LaporanKeuanganCluster;
use App\Services\LaporanKeuangan\RekapTagihanService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class RekapTagihanMahasiswa extends Page
{
    use HasPageShield;
    protected string $view = 'filament.pages.rekap-tagihan-mahasiswa';
    protected static ?string $cluster = LaporanKeuanganCluster::class;
    protected static ?string $navigationLabel = 'Rekap Tagihan Mahasiswa';
    protected static ?string $title = 'Rekap Tagihan Mahasiswa';
    protected static ?int $navigationSort = 1;

    public function __construct(
        protected readonly RekapTagihanService $service,
    ) {}
}
