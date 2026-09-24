<?php

uses(Tests\TestCase::class);

use App\Services\Akademik\NimService;
use App\Settings\KampusSettings;

function makeNimService(): NimService
{
    return new NimService(app(KampusSettings::class));
}

test('render mengikuti format {THN}{KODE}{NO:3}', function () {
    $nim = makeNimService()->render('{THN}{KODE}{NO:3}', 2024, '57401', 7);

    expect($nim)->toBe('2457401007');
});

test('render mendukung {TAHUN} tahun penuh', function () {
    $nim = makeNimService()->render('{TAHUN}{KODE}{NO:4}', 2024, '55', 3);

    expect($nim)->toBe('2024550003');
});

test('render memakai {NO} bawaan 3 digit ketika format tanpa lebar', function () {
    $nim = makeNimService()->render('{THN}-{NO}', 2026, 'X', 42);

    expect($nim)->toBe('26-042');
});

test('render mempertahankan karakter literal di format', function () {
    $nim = makeNimService()->render('{THN}.{KODE}.{NO:2}', 2025, '13', 9);

    expect($nim)->toBe('25.13.09');
});
