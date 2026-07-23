<?php

namespace App\Filament\Tables;

use App\Enums\KrsStatusEnum;
use App\Services\Laporan\RekapKrsService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Request;

/**
 * Table builder untuk Laporan Rekap KRS.
 *
 * Menggunakan ->records(Closure) untuk sumber data non-Eloquent (DTO
 * dari Service layer). PENTING: closure ini dieksekusi via sistem
 * auto-injection Filament (EvaluatesClosures), sehingga TIDAK BOLEH
 * dideklarasikan dengan parameter bernama seperti $filters — Filament
 * akan mencoba resolve parameter tersebut sebagai dependency dari
 * container dan mengirim null jika tidak ditemukan bindingnya. Filter
 * aktif diambil murni dari closure $getFilters yang di-capture via use().
 */
class RekapKrsTable
{
    public static function make(Table $table, \Closure $getFilters): Table
    {
        return $table
            ->records(function () use ($getFilters, $table) {
                $activeFilters = $getFilters();

                if (empty($activeFilters['tahun_akademik_id'])) {
                    return new LengthAwarePaginator([], 0, 10);
                }

                $result = app(RekapKrsService::class)->getData($activeFilters);
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

                TextColumn::make('jumlah_mata_kuliah')
                    ->label('Jml MK')
                    ->alignCenter()
                    ->badge(),

                TextColumn::make('total_sks')
                    ->label('Total SKS')
                    ->alignCenter()
                    ->weight('bold'),
                TextColumn::make('status_krs')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(KrsStatusEnum $state) => $state->getLabel())
                    ->color(fn(KrsStatusEnum $state) => $state->getColor())
                    ->icon(fn(KrsStatusEnum $state) => $state->getIcon()),
                TextColumn::make('nama_tahun_akademik')
                    ->label('Tahun Akademik')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nim')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
