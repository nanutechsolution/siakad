<?php

declare(strict_types=1);

namespace App\Services\Keuangan;

use App\Enums\Keuangan\StatusAdjustment;
use App\Exceptions\Keuangan\AdjustmentException;
use App\Models\KeuanganAdjustment;
use App\Models\User;

class AdjustmentStateMachine
{
    /**
     * Memvalidasi apakah transisi status diperbolehkan dan tidak melanggar aturan bisnis (seperti Maker-Checker).
     *
     * @throws AdjustmentException
     */
    public function assertCanTransition(KeuanganAdjustment $adjustment, StatusAdjustment $newStatus, User $user): void
    {
        $currentStatus = $adjustment->status;

        match ($newStatus) {
            StatusAdjustment::DIAJUKAN => $this->validatePengajuan($currentStatus),
            StatusAdjustment::DISETUJUI => $this->validatePersetujuan($adjustment, $currentStatus, $user),
            StatusAdjustment::DITOLAK => $this->validatePenolakan($currentStatus),
            StatusAdjustment::DIPOSTING => $this->validatePosting($currentStatus),
            StatusAdjustment::DIBATALKAN => $this->validatePembatalan($currentStatus),
            StatusAdjustment::DRAFT => throw new AdjustmentException('Tidak dapat mengembalikan status ke DRAFT.'),
        };
    }

    private function validatePengajuan(StatusAdjustment $currentStatus): void
    {
        if ($currentStatus !== StatusAdjustment::DRAFT) {
            throw new AdjustmentException("Hanya adjustment berstatus DRAFT yang dapat diajukan (Status saat ini: {$currentStatus->value}).");
        }
    }

    private function validatePersetujuan(KeuanganAdjustment $adjustment, StatusAdjustment $currentStatus, User $user): void
    {
        if ($currentStatus !== StatusAdjustment::DIAJUKAN) {
            throw new AdjustmentException("Hanya adjustment berstatus DIAJUKAN yang dapat disetujui (Status saat ini: {$currentStatus->value}).");
        }

        // Segregation of Duties: Maker tidak boleh menjadi Checker
        if ($adjustment->diajukan_oleh === $user->id) {
            throw new AdjustmentException('Pelanggaran Segregation of Duties: Anda tidak dapat menyetujui adjustment yang Anda ajukan sendiri.');
        }
    }

    private function validatePenolakan(StatusAdjustment $currentStatus): void
    {
        if ($currentStatus !== StatusAdjustment::DIAJUKAN) {
            throw new AdjustmentException("Hanya adjustment berstatus DIAJUKAN yang dapat ditolak (Status saat ini: {$currentStatus->value}).");
        }
    }

    private function validatePosting(StatusAdjustment $currentStatus): void
    {
        if ($currentStatus !== StatusAdjustment::DISETUJUI) {
            throw new AdjustmentException("Hanya adjustment berstatus DISETUJUI yang dapat diposting (Status saat ini: {$currentStatus->value}).");
        }
    }

    private function validatePembatalan(StatusAdjustment $currentStatus): void
    {
        // Pembatalan (tanpa pembalik) hanya bisa dilakukan sebelum diposting.
        // Jika sudah diposting, harus melalui record pembalik baru (akan dihandle di service lain).
        if ($currentStatus === StatusAdjustment::DIPOSTING) {
            throw new AdjustmentException('Adjustment yang sudah DIPOSTING tidak dapat dibatalkan langsung. Anda harus membuat Adjustment Pembalik.');
        }
    }
}
