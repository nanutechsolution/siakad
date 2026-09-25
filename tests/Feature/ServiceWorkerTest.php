<?php

test('service worker is a valid javascript file', function () {
    expect(is_file(public_path('sw.js')))->toBeTrue();
    expect(file_get_contents(public_path('sw.js')))->toContain("self.addEventListener('fetch'");
});

test('service worker never caches authenticated surfaces', function () {
    $source = file_get_contents(base_path('public/sw.js'));

    expect($source)->toContain('/admin');
    expect($source)->toContain('/dosen');
    expect($source)->toContain('/mahasiswa');
    expect($source)->toContain('/livewire/');
    expect($source)->toContain('/api/');
    expect($source)->toContain('/storage/');

    // Halaman navigasi harus selalu lolos ke network, tidak masuk cache.
    expect($source)->toContain("request.mode === 'navigate'");
});

test('panel providers register the service worker hook', function () {
    foreach (['Admin', 'Dosen', 'Mahasiswa'] as $panel) {
        $source = file_get_contents(
            app_path("Providers/Filament/{$panel}PanelProvider.php"),
        );

        expect($source)
            ->toContain('PanelsRenderHook::HEAD_END')
            ->toContain('PanelsRenderHook::BODY_END')
            ->toContain("components.pwa.head")
            ->toContain("components.pwa.register");
    }
});
