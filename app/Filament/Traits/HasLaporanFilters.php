<?php

declare(strict_types=1);

namespace App\Filament\Traits;

use App\Models\RefAngkatan;
use App\Models\RefFakultas;
use App\Models\RefProdi;
use App\Models\RefTahunAkademik;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;

/**
 * Trait shared filter form schema untuk semua Filament Report Pages.
 *
 * Menyediakan komponen filter standar: Tahun Akademik, Fakultas,
 * Program Studi (cascading dari Fakultas), dan Angkatan.
 *
 * Setiap Page yang menggunakan trait ini WAJIB mendefinisikan
 * property public array $filterState = [] untuk binding wire:model.
 */
trait HasLaporanFilters
{
    /**
     * Build filter grid schema. Panggil dari method schema() Page.
     */
    protected function buildFilterSchema(bool $withMahasiswa = false): array
    {
        return [
            Grid::make()
                ->schema(array_filter([
                    Select::make('filterState.tahun_akademik_id')
                        ->label('Tahun Akademik')
                        ->options(fn() => RefTahunAkademik::query()
                            ->orderByDesc('id')
                            ->get()
                            ->mapWithKeys(fn($ta) => [
                                $ta->id => "{$ta->nama_tahun} - Semester {$ta->semester}",
                            ])
                            ->toArray())
                        ->default(fn() => RefTahunAkademik::where('is_active', true)->value('id'))
                        ->required()
                        ->native(false)
                        ->live(),

                    Select::make('filterState.fakultas_id')
                        ->label('Fakultas')
                        ->options(fn() => RefFakultas::orderBy('nama_fakultas')
                            ->pluck('nama_fakultas', 'id')
                            ->toArray())
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(fn($state, callable $set) => $set('filterState.prodi_id', null)),

                    Select::make('filterState.prodi_id')
                        ->label('Program Studi')
                        ->options(function (callable $get) {
                            $query = RefProdi::query()->where('is_active', true);

                            if ($fakultasId = $get('filterState.fakultas_id')) {
                                $query->where('fakultas_id', $fakultasId);
                            }

                            return $query->orderBy('nama_prodi')->pluck('nama_prodi', 'id')->toArray();
                        })
                        ->native(false)
                        ->searchable()
                        ->live(),

                    Select::make('filterState.angkatan')
                        ->label('Angkatan')
                        ->options(fn() => RefAngkatan::orderByDesc('id_tahun')
                            ->pluck('id_tahun', 'id_tahun')
                            ->toArray())
                        ->native(false)
                        ->searchable()
                        ->live(),

                    $withMahasiswa ? Select::make('filterState.mahasiswa_id')
                        ->label('Mahasiswa')
                        ->searchable()
                        ->native(false)
                        ->getSearchResultsUsing(function (string $search) use ($withMahasiswa) {
                            return \App\Models\Mahasiswa::query()
                                ->where('nim', 'like', "%{$search}%")
                                ->orWhereHas('refPerson', fn($q) => $q->where('nama_lengkap', 'like', "%{$search}%"))
                                ->limit(20)
                                ->get()
                                ->mapWithKeys(fn($m) => [$m->id => "{$m->nim} - {$m->refPerson->nama_lengkap}"])
                                ->toArray();
                        })
                        ->getOptionLabelUsing(function ($value) {
                            $m = \App\Models\Mahasiswa::with('refPerson')->find($value);
                            return $m ? "{$m->nim} - {$m->refPerson->nama_lengkap}" : null;
                        })
                        ->live() : null,
                ])),
        ];
    }

    /**
     * Default filter state jika belum di-set.
     */
    protected function defaultFilterState(): array
    {
        return [
            'tahun_akademik_id' => RefTahunAkademik::where('is_active', true)->value('id'),
            'fakultas_id' => null,
            'prodi_id' => null,
            'angkatan' => null,
            'mahasiswa_id' => null,
        ];
    }
}
