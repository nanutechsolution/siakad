<?php

test('manifest is a valid web app manifest', function () {
    $manifestPath = public_path('manifest.webmanifest');

    expect(is_file($manifestPath))->toBeTrue();

    $manifest = json_decode(file_get_contents($manifestPath), true);

    expect($manifest)->toBeArray();
    expect($manifest['display'])->toBe('standalone');
    expect($manifest['start_url'])->toBe('/');
    expect($manifest['scope'])->toBe('/');
    expect($manifest['theme_color'])->toBe('#1e1b4b');
    expect($manifest['icons'])->not->toBeEmpty();
});

test('manifest icons are reachable', function () {
    $manifest = json_decode(
        file_get_contents(base_path('public/manifest.webmanifest')),
        true,
    );

    foreach ($manifest['icons'] as $icon) {
        $iconPath = public_path(ltrim(parse_url($icon['src'], PHP_URL_PATH), '/'));

        expect(is_file($iconPath))->toBeTrue();
        expect(mime_content_type($iconPath))->toBe('image/png');
    }
});

test('public views include pwa metadata', function () {
    expect(file_get_contents(resource_path('views/welcome.blade.php')))
        ->toContain("@include('components.pwa.head')")
        ->toContain("@include('components.pwa.register')");

    expect(file_get_contents(resource_path('views/panduan-krs.blade.php')))
        ->toContain("@include('components.pwa.head')")
        ->toContain("@include('components.pwa.register')");
});
