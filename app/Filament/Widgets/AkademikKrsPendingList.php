<?php

namespace App\Filament\Widgets;

use App\Models\Krs;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\TableWidget;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Services\Akademik\DashboardAkademikService;

class AkademikKrsPendingList extends TableWidget
{
    use HasWidgetShield;
    protected int|string|array $columnSpan = 'full';
    protected ?string $pollingInterval = '60s';

    public function table(Table $table): Table
    {
        return $table
            // visibleTo(): Admin Prodi hanya melihat antrean KRS Prodi-nya.
            ->query(fn() => app(DashboardAkademikService::class)->krsQuery()
                ->where('status_krs', 'DIAJUKAN')
                ->orderByDesc('diajukan_at')
                ->limit(10)
                ->with(['mahasiswa.person', 'mahasiswa.prodi', 'tahunAkademik']))
            ->columns([
                TextColumn::make('mahasiswa.nim')->label('NIM')->copyable()->fontFamily('mono'),
                TextColumn::make('mahasiswa.person.nama_lengkap')->label('Mahasiswa'),
                TextColumn::make('mahasiswa.prodi.nama_prodi')->label('Prodi'),
                TextColumn::make('mahasiswa.angkatan_id')->label('Angkatan'),
                TextColumn::make('tahunAkademik.nama_tahun')->label('Periode'),
                TextColumn::make('diajukan_at')->label('Diajukan')->dateTime('d M Y, H:i'),
            ])
            ->paginated([5, 10]);
    }

    public function getTableHeading(): string
    {
        return 'KRS Menunggu Persetujuan';
    }
}
