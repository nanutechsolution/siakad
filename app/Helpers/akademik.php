<?php

declare(strict_types=1);

use App\Models\RefTahunAkademik;
use App\Services\TahunAkademikService;

if (! function_exists('active_ta')) {
    /**
     * Helper untuk mendapatkan instance RefTahunAkademik yang aktif saat ini.
     */
    function active_ta(): RefTahunAkademik
    {
        return app(TahunAkademikService::class)->getActive();
    }
}

if (! function_exists('active_ta_id')) {
    /**
     * Helper cepat untuk mendapatkan ID Tahun Akademik aktif.
     */
    function active_ta_id(): int
    {
        return app(TahunAkademikService::class)->getActiveId();
    }
}