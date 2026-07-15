<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RefTahunAkademik;
use Illuminate\Support\Facades\Cache;

class TahunAkademikService
{
    private const string CACHE_KEY = 'siakad_active_tahun_akademik_id';
    private const int CACHE_TTL = 86400;

    public function getActive(): RefTahunAkademik
    {
        return RefTahunAkademik::where('is_active', true)->first()
            ?? RefTahunAkademik::orderByDesc('tahun_keluar')
            ->orderByDesc('semester')
            ->firstOrFail();
    }


    public function getActiveCached(): RefTahunAkademik
    {
        $id = Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL,
            fn() => $this->getActive()->id
        );

        return RefTahunAkademik::findOrFail($id);
    }


    public function getActiveId(): int
    {
        return Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL,
            fn() => $this->getActive()->id
        );
    }


    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
