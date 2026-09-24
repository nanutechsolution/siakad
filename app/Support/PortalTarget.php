<?php

declare(strict_types=1);

namespace App\Support;

use Filament\Facades\Filament;

/**
 * Menentukan URL portal ALTERNATIF untuk user yang punya akses dua panel
 * (mis. dosen yang juga Admin Prodi).
 *
 * Dipakai oleh user menu di kedua panel provider supaya label/url/kondisi
 * tampil-nya identik dan tidak digandakan secara manual di setiap panel.
 */
final class PortalTarget
{
    /**
     * @return array{url: string, label: string, description: string, icon: string}|null
     *         null kalau user tidak punya portal lain yang bisa dimasuki.
     */
    public static function current(): ?array
    {
        $user = auth()->user();
        $currentPanel = Filament::getCurrentPanel()?->getId();

        if ($user === null || $currentPanel === null) {
            return null;
        }

        // Sedang berada di panel Dosen -> tawarkan panel Admin (jika berhak).
        if ($currentPanel === 'dosen' && $user->canAccessAdmin()) {
            return [
                'url' => Filament::getPanel('admin')->getUrl(),
                'label' => 'Portal Admin / BAUK',
                'description' => 'Keuangan, administrasi, laporan akademik.',
                'icon' => 'heroicon-o-building-office',
            ];
        }

        // Sedang berada di panel Admin -> tawarkan panel Dosen (jika dosen).
        if ($currentPanel === 'admin' && $user->isDosen()) {
            return [
                'url' => Filament::getPanel('dosen')->getUrl(),
                'label' => 'Portal Dosen',
                'description' => 'Jadwal, nilai, dan bimbingan mahasiswa.',
                'icon' => 'heroicon-o-academic-cap',
            ];
        }

        return null;
    }

    /**
     * Item untuk user menu Filament.
     *
     * WAJIB selalu mengembalikan Action — tidak pernah null. HasUserMenu
     * memanggil $action->getName() tanpa cek, sehingga closure yang
     * mengembalikan null membuat login user tanpa portal lain
     * (mis. super_admin) error 500. Sembunyikan lewat ->visible() saja.
     */
    public static function menuItem(): \Filament\Actions\Action
    {
        $target = self::current();

        $action = \Filament\Actions\Action::make('portal-switcher')
            ->label($target['label'] ?? 'Portal lain')
            ->icon($target['icon'] ?? 'heroicon-o-arrow-right-circle')
            ->sort(-50);

        if ($target === null) {
            return $action->visible(false);
        }

        return $action
            ->label($target['label'])
            ->icon($target['icon'])
            ->tooltip($target['description'])
            ->url($target['url']);
    }
}
