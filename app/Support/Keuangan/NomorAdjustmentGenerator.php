<?php

declare(strict_types=1);

namespace App\Support\Keuangan;

use Illuminate\Support\Facades\DB;

class NomorAdjustmentGenerator
{
    /**
     * Generate nomor adjustment secara aman dengan mencegah race condition (lockForUpdate).
     * Format: ADJ/YYYY/MM/00001
     */
    public static function generate(): string
    {
        return DB::transaction(function () {
            $prefix = 'ADJ/' . now()->format('Y/m') . '/';

            $latest = DB::table('keuangan_adjustments')
                ->where('nomor_adjustment', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('nomor_adjustment', 'desc')
                ->first();

            if (! $latest) {
                return $prefix . '00001';
            }

            $lastSequence = (int) substr($latest->nomor_adjustment, -5);
            $nextSequence = $lastSequence + 1;

            return $prefix . str_pad((string) $nextSequence, 5, '0', STR_PAD_LEFT);
        });
    }
}