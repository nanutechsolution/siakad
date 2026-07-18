<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Domain\Authorization\Services\JabatanContextResolver;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

/**
 * Halaman wajib singgah untuk user dengan >1 jabatan aktif sebelum masuk
 * panel. Tidak muncul di menu navigasi (shouldRegisterNavigation = false) —
 * hanya diakses via redirect dari EnsureOrganizationContext middleware.
 *
 * Daftarkan di Panel Provider terkait:
 *     ->pages([\App\Filament\Pages\PilihKonteksKerja::class])
 */
final class PilihKonteksKerja extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.pilih-konteks-kerja';

    public Collection $contexts;

    public function mount(): void
    {
        $resolver = app(JabatanContextResolver::class);
        $user = auth()->user();

        $this->contexts = $resolver->availableContexts($user);

        // Jika ternyata cuma 0/1 konteks (mis. diakses langsung lewat URL),
        // tidak ada yang perlu dipilih -> lempar balik ke dashboard panel.
        if ($this->contexts->count() <= 1) {
            $this->redirect(Filament::getUrl());
        }
    }

    public function pilihKonteks(int $trxPersonJabatanId): void
    {
        app(JabatanContextResolver::class)->setActiveContext(auth()->user(), $trxPersonJabatanId);

        $this->redirect(Filament::getUrl());
    }
}
