<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Observers;

use App\Domain\Authorization\Services\DosenWaliRoleSyncService;
use App\Models\KelasDosenWali;

final class KelasDosenWaliObserver
{
    public function __construct(
        private readonly DosenWaliRoleSyncService $syncService,
    ) {}

    public function created(KelasDosenWali $kelasDosenWali): void
    {
        $this->syncService->syncOnAssign($kelasDosenWali);
    }

    public function deleted(KelasDosenWali $kelasDosenWali): void
    {
        $this->syncService->syncOnRemove($kelasDosenWali);
    }
}
