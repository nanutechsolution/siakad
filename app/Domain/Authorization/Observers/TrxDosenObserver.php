<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Observers;

use App\Domain\Authorization\Services\DosenRoleSyncService;
use App\Models\TrxDosen;

final class TrxDosenObserver
{
    public function __construct(
        private readonly DosenRoleSyncService $syncService,
    ) {}

    public function created(TrxDosen $dosen): void
    {
        if ($dosen->is_active) {
            $this->syncService->syncOnActivate($dosen);
        }
    }

    public function updated(TrxDosen $dosen): void
    {
        if (!$dosen->wasChanged('is_active')) {
            return;
        }

        if ($dosen->is_active) {
            $this->syncService->syncOnActivate($dosen);
        } else {
            $this->syncService->syncOnDeactivate($dosen);
        }
    }

    public function deleted(TrxDosen $dosen): void
    {
        $this->syncService->syncOnDeactivate($dosen);
    }
}
