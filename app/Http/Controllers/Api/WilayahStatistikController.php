<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\StatusKuliah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WilayahStatistikController
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'level' => ['sometimes', 'string', Rule::in(['provinsi', 'kabupaten', 'kecamatan', 'desa'])],
            'parent' => ['nullable', 'string', 'max:20', 'regex:/^[0-9.]+$/'],
            'status' => ['sometimes', 'string', Rule::in(['aktif', 'semua'])],
        ]);

        $level = $validated['level'] ?? 'provinsi';
        $parent = $validated['parent'] ?? null;
        $status = $validated['status'] ?? 'aktif';

        $cacheKey = "wilayah-statistik:{$level}:{$parent}:{$status}";
        $result = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($level, $parent, $status): array {
            $query = DB::table('mahasiswas as m')
                ->join('mahasiswa_biodata as mb', 'mb.mahasiswa_id', '=', 'm.id')
                ->join('villages as v', 'v.id', '=', 'mb.village_id')
                ->join('districts as d', 'd.id', '=', 'v.district_id')
                ->join('regencies as r', 'r.id', '=', 'd.regency_id')
                ->join('provinces as p', 'p.id', '=', 'r.province_id')
                ->whereNull('m.deleted_at') // query builder mentah tidak otomatis mengkecualikan soft delete
                ->selectRaw($this->groupExpression($level))
                ->selectRaw('COUNT(DISTINCT m.id) as jumlah_mahasiswa');

            if ($status === 'aktif') {
                $query->whereExists(function ($subquery): void {
                    $subquery->selectRaw('1')
                        ->from('riwayat_status_mahasiswas as rsm')
                        ->join('ref_tahun_akademik as rta', 'rta.id', '=', 'rsm.tahun_akademik_id')
                        ->whereColumn('rsm.mahasiswa_id', 'm.id')
                        ->where('rsm.status_kuliah', StatusKuliah::AKTIF->value)
                        ->where('rta.is_active', true);
                });
            }

            if ($parent !== null) {
                $parentColumn = match ($level) {
                    'kabupaten' => 'p.code',
                    'kecamatan' => 'r.code',
                    'desa' => 'd.code',
                    default => null,
                };

                if ($parentColumn !== null) {
                    $query->where($parentColumn, $parent);
                }
            }

            $query->groupBy(...$this->groupColumns($level));

            return [
                'data' => $query
                    ->orderByDesc('jumlah_mahasiswa')
                    ->orderBy('name')
                    ->get()
                    ->map(static fn (object $row): array => [
                        'code' => $row->code,
                        'name' => $row->name,
                        'jumlah_mahasiswa' => (int) $row->jumlah_mahasiswa,
                    ])
                    ->all(),
                'meta' => [
                    'level' => $level,
                    'status' => $status,
                    'generated_at' => now()->toIso8601String(),
                ],
            ];
        });

        $result['meta']['total'] = count($result['data']);

        return response()->json($result);
    }

    private function groupExpression(string $level): string
    {
        return match ($level) {
            'provinsi' => 'p.code as code, p.name as name',
            'kabupaten' => 'r.code as code, r.name as name',
            'kecamatan' => 'd.code as code, d.name as name',
            'desa' => 'v.code as code, v.name as name',
        };
    }

    /** @return list<string> */
    private function groupColumns(string $level): array
    {
        return match ($level) {
            'provinsi' => ['p.code', 'p.name'],
            'kabupaten' => ['r.code', 'r.name'],
            'kecamatan' => ['d.code', 'd.name'],
            'desa' => ['v.code', 'v.name'],
        };
    }
}
