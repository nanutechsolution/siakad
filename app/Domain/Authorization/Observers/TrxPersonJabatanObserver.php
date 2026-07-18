<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Observers;

use App\Domain\Authorization\Services\JabatanRoleSyncService;
use App\Models\TrxPersonJabatan;

final class TrxPersonJabatanObserver
{
    public function __construct(
        private readonly JabatanRoleSyncService $syncService,
    ) {}

    public function created(TrxPersonJabatan $jabatan): void
    {
        if ($this->isCurrentlyActive($jabatan)) {
            $this->syncService->syncOnAssign($jabatan);
        }
    }

    public function updated(TrxPersonJabatan $jabatan): void
    {
        if (!$jabatan->wasChanged(['tanggal_selesai', 'jabatan_id', 'person_id'])) {
            return;
        }

        if ($this->isCurrentlyActive($jabatan)) {
            $this->syncService->syncOnAssign($jabatan);
        } else {
            $this->syncService->syncOnEnd($jabatan);
        }
    }

    public function deleted(TrxPersonJabatan $jabatan): void
    {
        $this->syncService->syncOnEnd($jabatan);
    }

    private function isCurrentlyActive(TrxPersonJabatan $jabatan): bool
    {
        $today = now()->toDateString();

        return $jabatan->tanggal_mulai <= $today
            && ($jabatan->tanggal_selesai === null || $jabatan->tanggal_selesai >= $today);
    }
}
