<?php

declare(strict_types=1);

use App\Contracts\WilayahSourceInterface;
use App\Models\District;
use App\Models\MahasiswaBiodata;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use App\Services\Wilayah\WilayahImporter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

uses()->group('wilayah');

beforeEach(function (): void {
    Schema::disableForeignKeyConstraints();
    Schema::dropIfExists('mahasiswa_biodata');
    Schema::dropIfExists('villages');
    Schema::dropIfExists('districts');
    Schema::dropIfExists('regencies');
    Schema::dropIfExists('provinces');
    Schema::create('provinces', function (Blueprint $table): void {
        $table->id();
        $table->string('code')->unique();
        $table->string('name');
        $table->timestamps();
    });
    Schema::create('regencies', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('province_id');
        $table->string('code')->unique();
        $table->string('name');
        $table->timestamps();
    });
    Schema::create('districts', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('regency_id');
        $table->string('code')->unique();
        $table->string('name');
        $table->timestamps();
    });
    Schema::create('villages', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('district_id');
        $table->string('code')->unique();
        $table->string('name');
        $table->string('type');
        $table->timestamps();
    });
    Schema::create('mahasiswa_biodata', function (Blueprint $table): void {
        $table->id();
        $table->char('mahasiswa_id', 36)->unique();
        $table->text('alamat_ktp')->nullable();
        $table->text('alamat_domisili')->nullable();
        $table->string('kode_pos', 10)->nullable();
        $table->unsignedBigInteger('village_id')->nullable();
        $table->timestamps();
    });
    Schema::enableForeignKeyConstraints();
});

// Tanpa RefreshDatabase (migration proyek belum kompatibel SQLite), tiap test dibersihkan manual.
afterEach(function (): void {
    Schema::disableForeignKeyConstraints();
    foreach (['mahasiswa_biodata', 'villages', 'districts', 'regencies', 'provinces'] as $table) {
        Schema::dropIfExists($table);
    }
    Schema::enableForeignKeyConstraints();
});

final class FakeWilayahSource implements WilayahSourceInterface
{
    public function __construct(
        public array $provinces = [],
        public array $regencies = [],
        public array $districts = [],
        public array $villages = [],
    ) {
    }

    public function provinces(): iterable
    {
        return $this->provinces;
    }

    public function regencies(): iterable
    {
        return $this->regencies;
    }

    public function districts(): iterable
    {
        return $this->districts;
    }

    public function villages(): iterable
    {
        return $this->villages;
    }
}

function sampleSource(): FakeWilayahSource
{
    return new FakeWilayahSource(
        provinces: [['code' => '52', 'name' => 'Nusa Tenggara Timur']],
        regencies: [
            ['code' => '52.01', 'name' => 'Kota Kupang', 'province_code' => '52'],
            ['code' => '52.20', 'name' => 'Sumba Barat Daya', 'province_code' => '52'],
        ],
        districts: [
            ['code' => '52.20.04', 'name' => 'Loura', 'regency_code' => '52.20'],
        ],
        villages: [
            [
                'code' => '52.20.04.2001',
                'name' => 'Watu Kaka',
                'type' => 'desa',
                'district_code' => '52.20.04',
            ],
        ],
    );
}

it('imports all four administrative levels', function (): void {
    $result = app(WilayahImporter::class)->import(sampleSource());

    expect(Province::query()->count())->toBe(1)
        ->and(Province::query()->first()->code)->toBe('52')
        ->and(Regency::query()->count())->toBe(2)
        ->and(District::query()->count())->toBe(1)
        ->and(Village::query()->count())->toBe(1)
        ->and($result['summary']['provinces']['inserted'])->toBe(1)
        ->and($result['summary']['villages']['inserted'])->toBe(1)
        ->and($result['errors'])->toBe([]);
});

it('does not create duplicates on a second import', function (): void {
    $importer = app(WilayahImporter::class);
    $importer->import(sampleSource());
    $second = $importer->import(sampleSource());

    expect(Province::query()->count())->toBe(1)
        ->and(Regency::query()->count())->toBe(2)
        ->and(District::query()->count())->toBe(1)
        ->and(Village::query()->count())->toBe(1)
        ->and($second['summary']['provinces']['inserted'])->toBe(0)
        ->and($second['summary']['provinces']['updated'])->toBe(0)
        ->and($second['summary']['villages']['updated'])->toBe(0);
});

it('updates an existing region when the name changes', function (): void {
    $importer = app(WilayahImporter::class);
    $importer->import(sampleSource());

    $changed = sampleSource();
    $changed->regencies[1]['name'] = 'Sumba Barat Daya Baru';
    $result = $importer->import($changed);

    expect(Regency::query()->count())->toBe(2)
        ->and(Regency::query()->where('code', '52.20')->first()->name)->toBe('Sumba Barat Daya Baru')
        ->and($result['summary']['regencies']['updated'])->toBe(1)
        ->and($result['summary']['regencies']['inserted'])->toBe(0);
});

it('rejects a child row with an unknown parent and rolls back', function (): void {
    $source = new FakeWilayahSource(
        provinces: [['code' => '52', 'name' => 'Nusa Tenggara Timur']],
        regencies: [['code' => '99.99', 'name' => 'Tanpa Provinsi', 'province_code' => '99']],
    );

    expect(fn () => app(WilayahImporter::class)->import($source))
        ->toThrow(\App\Services\Wilayah\WilayahImportValidationException::class)
        ->and(Province::query()->count())->toBe(0)
        ->and(Regency::query()->count())->toBe(0);
});

it('links village up through district, regency, and province', function (): void {
    app(WilayahImporter::class)->import(sampleSource());

    $village = Village::query()->with('district.regency.province')->first();

    expect($village->district->name)->toBe('Loura')
        ->and($village->district->regency->name)->toBe('Sumba Barat Daya')
        ->and($village->district->regency->province->name)->toBe('Nusa Tenggara Timur')
        ->and($village->district->regency->province->code)->toBe('52');
});

it('relates mahasiswa biodata to a village', function (): void {
    app(WilayahImporter::class)->import(sampleSource());

    $mahasiswa = (object) ['id' => 'test-mahasiswa-id'];
    $biodata = MahasiswaBiodata::create([
        'mahasiswa_id' => $mahasiswa->id,
        'village_id' => Village::query()->first()->id,
        'alamat_ktp' => 'Jalan contoh',
    ]);

    expect($biodata->village)->toBeInstanceOf(Village::class)
        ->and($biodata->village->code)->toBe('52.20.04.2001')
        ->and($biodata->village->district->regency->code)->toBe('52.20');
});

it('keeps existing address fields when village_id is not supplied', function (): void {
    $mahasiswa = (object) ['id' => 'test-mahasiswa-id'];
    $biodata = MahasiswaBiodata::create([
        'mahasiswa_id' => $mahasiswa->id,
        'alamat_ktp' => 'Jalan lama',
        'kode_pos' => '88111',
    ]);

    expect($biodata->village_id)->toBeNull()
        ->and($biodata->alamat_ktp)->toBe('Jalan lama')
        ->and($biodata->kode_pos)->toBe('88111');
});
