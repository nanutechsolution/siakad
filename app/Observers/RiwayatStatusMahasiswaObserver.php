<?php

namespace App\Observers;

use App\Models\RiwayatStatusMahasiswa;
use Illuminate\Support\Facades\Cache;

class RiwayatStatusMahasiswaObserver
{
    /**
     * Naikkan versi cache Monitoring KRS.
     */
    protected function invalidateMonitoringCache(): void
    {
        Cache::increment('monitoring-krs:version');
    }

    public function created(RiwayatStatusMahasiswa $riwayatStatus): void
    {
        $this->invalidateMonitoringCache();
    }

    public function updated(RiwayatStatusMahasiswa $riwayatStatus): void
    {
        $this->invalidateMonitoringCache();
    }

    public function deleted(RiwayatStatusMahasiswa $riwayatStatus): void
    {
        $this->invalidateMonitoringCache();
    }

    public function restored(RiwayatStatusMahasiswa $riwayatStatus): void
    {
        $this->invalidateMonitoringCache();
    }

    public function forceDeleted(RiwayatStatusMahasiswa $riwayatStatus): void
    {
        $this->invalidateMonitoringCache();
    }
}