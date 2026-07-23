<?php

declare(strict_types=1);

namespace App\Application\Migration\Services;

use Illuminate\Support\Facades\Cache;

final class MigrationCancellationService
{
    private function cacheKey(int $batchId): string
    {
        return "migration_batch_cancel_requested:{$batchId}";
    }

    public function requestCancel(int $batchId): void
    {
        Cache::put($this->cacheKey($batchId), true, now()->addHours(6));
    }

    public function isCancelRequested(int $batchId): bool
    {
        return Cache::get($this->cacheKey($batchId), false) === true;
    }

    public function clear(int $batchId): void
    {
        Cache::forget($this->cacheKey($batchId));
    }
}