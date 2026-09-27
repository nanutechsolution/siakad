<?php

declare(strict_types=1);

namespace App\Services\Wilayah;

use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use App\Contracts\WilayahSourceInterface;
use Illuminate\Support\Facades\DB;
use App\Services\Wilayah\WilayahImportValidationException;


final class WilayahImporter
{
    /** @var array<string, array{inserted: int, updated: int}> */
    private array $summary = [
        'provinces' => ['inserted' => 0, 'updated' => 0],
        'regencies' => ['inserted' => 0, 'updated' => 0],
        'districts' => ['inserted' => 0, 'updated' => 0],
        'villages' => ['inserted' => 0, 'updated' => 0],
    ];

    /** @var list<string> */
    private array $errors = [];

    public function import(WilayahSourceInterface $source): array
    {
        $this->summary = array_map(
            static fn (array $counts): array => ['inserted' => 0, 'updated' => 0],
            $this->summary,
        );
        $this->errors = [];

        DB::transaction(function () use ($source): void {
            $this->importProvinces($source->provinces());
            $this->importRegencies($source->regencies());
            $this->importDistricts($source->districts());
            $this->importVillages($source->villages());

            if ($this->errors !== []) {
                throw new WilayahImportValidationException($this->errors);
            }
        });

        return ['summary' => $this->summary, 'errors' => $this->errors];
    }

    private function importProvinces(iterable $rows): void
    {
        foreach ($rows as $row) {
            $data = $this->requireFields($row, ['code', 'name'], 'province');
            $this->upsert(Province::query(), $data['code'], ['name' => $data['name']], 'provinces');
        }
    }

    private function importRegencies(iterable $rows): void
    {
        foreach ($rows as $row) {
            $data = $this->requireFields($row, ['code', 'name', 'province_code'], 'regency');
            $province = Province::query()->where('code', $data['province_code'])->first();

            if ($province === null) {
                $this->errors[] = "Regency {$data['code']} references unknown province {$data['province_code']}.";
                continue;
            }

            $this->upsert(Regency::query(), $data['code'], [
                'province_id' => $province->id,
                'name' => $data['name'],
            ], 'regencies');
        }
    }

    private function importDistricts(iterable $rows): void
    {
        foreach ($rows as $row) {
            $data = $this->requireFields($row, ['code', 'name', 'regency_code'], 'district');
            $regency = Regency::query()->where('code', $data['regency_code'])->first();

            if ($regency === null) {
                $this->errors[] = "District {$data['code']} references unknown regency {$data['regency_code']}.";
                continue;
            }

            $this->upsert(District::query(), $data['code'], [
                'regency_id' => $regency->id,
                'name' => $data['name'],
            ], 'districts');
        }
    }

    private function importVillages(iterable $rows): void
    {
        foreach ($rows as $row) {
            $data = $this->requireFields($row, ['code', 'name', 'type', 'district_code'], 'village');
            $district = District::query()->where('code', $data['district_code'])->first();

            if ($district === null) {
                $this->errors[] = "Village {$data['code']} references unknown district {$data['district_code']}.";
                continue;
            }

            $this->upsert(Village::query(), $data['code'], [
                'district_id' => $district->id,
                'name' => $data['name'],
                'type' => $data['type'],
            ], 'villages');
        }
    }

    /** @param array<string, mixed> $row @param list<string> $fields @return array<string, string> */
    private function requireFields(array $row, array $fields, string $level): array
    {
        $data = [];

        foreach ($fields as $field) {
            $value = $row[$field] ?? null;
            if (! is_string($value) || trim($value) === '') {
                $this->errors[] = ucfirst($level) . " row is missing {$field}.";
                continue;
            }
            $data[$field] = trim($value);
        }

        return $data + array_fill_keys($fields, '');
    }

    private function upsert($query, string $code, array $values, string $level): void
    {
        $model = $query->where('code', $code)->first();

        if ($model === null) {
            $model = $query->create(['code' => $code] + $values);
            $this->summary[$level]['inserted']++;
            return;
        }

        if ($model->fill($values)->isDirty()) {
            $model->save();
            $this->summary[$level]['updated']++;
        }
    }
}
