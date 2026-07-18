<?php

namespace App\Policies;

use App\Models\User;

/**
 * Policy khusus halaman (bukan model Eloquent), didaftarkan manual di
 * AuthServiceProvider karena Monitoring KRS adalah Filament Custom Page,
 * bukan Resource — Shield tidak generate policy ini otomatis.
 *
 * Permission `view_monitoring_krs` dan `export_monitoring_krs` didaftarkan
 * lewat Shield (lihat README.md di root module ini).
 */
class MonitoringKrsPolicy
{
    public function view(User $user): bool
    {
        return $user->can('view_monitoring_krs');
    }

    public function export(User $user): bool
    {
        return $user->can('export_monitoring_krs');
    }
}