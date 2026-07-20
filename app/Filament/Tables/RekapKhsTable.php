<?php

namespace App\Filament\Tables;

use App\Services\Laporan\RekapKhsService;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Request;

/**
 * Table builder untuk Laporan Rekap KHS.
 *
 * Menggunakan ->records(Closure) untuk sumber data non-Eloquent (DTO
 * dari Service layer). PENTING: closure ini dieksekusi via sistem
 * auto-injection Filament (EvaluatesClosures), sehingga TIDAK BOLEH
 * dideklarasikan dengan parameter bernama seperti $filters — Filament
 * akan mencoba resolve parameter tersebut sebagai dependency dari
 * container dan mengirim null jika tidak ditemukan bindingnya. Filter
 * aktif diambil murni dari closure $getFilters yang di-capture via use().
 */
class RekapKhsTable
{
    public static function make(Table $table, \Closure $getFilters): Table
    {
        return $table
            ->records(function () use ($getFilters, $table) {
                $activeFilters = $getFilters();

                if (empty($activeFilters['tahun_akademik_id'])) {
                    return new LengthAwarePaginator([], 0, 10);
                }

                $result = app(RekapKhsService::class)->getData($activeFilters);
                $items = collect($result['data'])->map->toArray()->values();

                $livewire = $table->getLivewire();
                $perPage = (int) (property_exists($livewire, 'tableRecordsPerPage')
                    ? ($livewire->tableRecordsPerPage ?: 25)
                    : 25);
                $page = (int) (
                    method_exists($livewire, 'getTablePage')
                    ? $livewire->getTablePage()
                    : ($livewire->page ?? Request::get('page', 1))
                );
                $slice = $items->slice(($page - 1) * $perPage, $perPage)->values();

                return new LengthAwarePaginator(
                    $slice,
                    $items->count(),
                    $perPage,
                    $page,
                    ['path' => Request::url(), 'query' => Request::query()]
                );
            })
            ->columns([
                TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('nama_mahasiswa')
                    ->label('Nama Mahasiswa')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('nama_prodi')
                    ->label('Prodi')
                    ->badge()
                    ->color('info'),

                TextColumn::make('angkatan')
                    ->label('Angkatan')
                    ->sortable(),

                TextColumn::make('semester')
                    ->label('Semester')
                    ->alignCenter(),

                TextColumn::make('ips')
                    ->label('IPS')
                    ->alignCenter()
                    ->weight('bold')
                    ->color(fn($state) => match (true) {
                        $state >= 3.0 => 'success',
                        $state >= 2.0 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn($state) => number_format((float) $state, 2)),

                TextColumn::make('sks_semester')
                    ->label('SKS Semester')
                    ->alignCenter(),

                TextColumn::make('sks_total')
                    ->label('SKS Kumulatif')
                    ->alignCenter(),

                TextColumn::make('status_akademik')
                    ->label('Status Akademik')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Sangat Memuaskan', 'Memuaskan' => 'success',
                        'Baik', 'Cukup' => 'warning',
                        default => 'danger',
                    }),

                TextColumn::make('nama_tahun_akademik')
                    ->label('Tahun Akademik')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nim')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
