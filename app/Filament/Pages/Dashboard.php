<?php

namespace App\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;

/**
 * Override Dashboard bawaan Filament.
 *
 * Sengaja tetap extends BaseDashboard (bukan Page biasa) supaya class ini
 * tetap mewarisi routePath '/' — dibutuhkan secara internal oleh Filament
 * untuk redirect setelah login, breadcrumb "home", link logo, dsb.
 *
 * $shouldRegisterNavigation = false membuat menu "Dasbor" hilang dari
 * sidebar tanpa merusak route root panel.
 *
 * mount() di-override untuk redirect ke dashboard pertama yang memang
 * DIIZINKAN (via Filament Shield) untuk role user yang login. Urutan
 * prioritas didefinisikan di $candidateDashboards di bawah — tinggal
 * tambah/kurangi/urutkan sesuai kebutuhan role baru di masa depan.
 *
 * Kalau tidak ada satupun dashboard yang diizinkan untuk role user
 * (mis. user baru yang belum di-assign role apapun), TIDAK di-redirect
 * paksa (itu akan berujung 403 dari Shield). Sebagai gantinya tampilkan
 * halaman kosong dengan notifikasi yang jelas.
 */
class Dashboard extends BaseDashboard
{
    protected static bool $shouldRegisterNavigation = false;

    /**
     * Daftar kandidat dashboard, urut dari prioritas tertinggi.
     * Tambahkan class dashboard baru di sini kalau nanti dibuat lagi
     * (mis. DashboardSdm::class, DashboardLpm::class, dst).
     *
     * @var array<class-string<BaseDashboard>>
     */
    protected static array $candidateDashboards = [
        DashboardAkademik::class,
        DashboardKeuangan::class,
    ];

    public function mount(): void
    {
        foreach (static::$candidateDashboards as $dashboard) {
            if ($dashboard::canAccess()) {
                $this->redirect($dashboard::getUrl());

                return;
            }
        }

        Notification::make()
            ->warning()
            ->title('Belum ada dashboard yang dapat diakses')
            ->body('Akun kamu belum memiliki hak akses ke dashboard manapun. Hubungi Administrator untuk pengaturan role & permission.')
            ->persistent()
            ->send();
    }

    public function getWidgets(): array
    {
        return [];
    }

    public function getColumns(): int|array
    {
        return 1;
    }
}