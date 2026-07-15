<?php

namespace App\Observers;

use App\Models\RefTahunAkademik;
use App\Services\TahunAkademikService;

class TahunAkademikObserver
{
    public function __construct(protected TahunAkademikService $service) {}

    public function saved(RefTahunAkademik $refTahunAkademik): void
    {
        // Setiap kali ada perubahan simpan/update status TA, bersihkan cache data lama
        $this->service->clearCache();
    }

    public function deleted(RefTahunAkademik $refTahunAkademik): void
    {
        $this->service->clearCache();
    }
}
