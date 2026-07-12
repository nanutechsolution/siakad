<?php

namespace App\Filament\Dosen\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class PortalSwitcher extends Widget
{
    protected  string $view = 'filament.dosen.widgets.portal-switcher';

    protected int|string|array $columnSpan = 'full';
    public static function canView(): bool
    {
        $user = auth()->user();

        $panel = Filament::getCurrentPanel()->getId();

        return match ($panel) {

            'dosen' =>
            $user->canAccessAdmin(),

            'admin' =>
            $user->isDosen(),

            default => false,
        };
    }

    public function getViewData(): array
    {
        $user = Auth::user();

        $currentPanel = Filament::getCurrentPanel()->getId();

        $portals = [];


        // Jangan tampilkan portal yang sedang aktif
        if (
            $user->isDosen()
            && $currentPanel !== 'dosen'
        ) {

            $portals[] = [
                'name' => 'Portal Dosen',
                'description' => 'Jadwal, nilai, bimbingan mahasiswa',
                'url' => Filament::getPanel('dosen')->getUrl(),
                'icon' => 'heroicon-o-academic-cap',
            ];
        }


        // Admin / BAUK
        if (
            $user->canAccessAdmin()
            && $currentPanel !== 'admin'
        ) {

            $portals[] = [
                'name' => 'Portal Admin / BAUK',
                'description' => 'Keuangan, administrasi, laporan',
                'url' => Filament::getPanel('admin')->getUrl(),
                'icon' => 'heroicon-o-building-office',
            ];
        }


        return [
            'portals' => $portals,
        ];
    }
}
