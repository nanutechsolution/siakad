<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Observers;

use App\Domain\Authorization\Services\MahasiswaRoleSyncService;
use App\Models\Mahasiswa;

final class MahasiswaObserver
{
    public function __construct(
        private readonly MahasiswaRoleSyncService $syncService,
    ) {
    }

    public function created(Mahasiswa $mahasiswa): void
    {
        $this->syncService->syncOnCreate($mahasiswa);
    }
}