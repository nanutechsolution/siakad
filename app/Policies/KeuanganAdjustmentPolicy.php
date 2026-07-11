<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\KeuanganAdjustment;
use Illuminate\Auth\Access\HandlesAuthorization;

class KeuanganAdjustmentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:KeuanganAdjustment');
    }

    public function view(AuthUser $authUser, KeuanganAdjustment $keuanganAdjustment): bool
    {
        return $authUser->can('View:KeuanganAdjustment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:KeuanganAdjustment');
    }

    public function update(AuthUser $authUser, KeuanganAdjustment $keuanganAdjustment): bool
    {
        return $authUser->can('Update:KeuanganAdjustment');
    }

    public function delete(AuthUser $authUser, KeuanganAdjustment $keuanganAdjustment): bool
    {
        return $authUser->can('Delete:KeuanganAdjustment');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:KeuanganAdjustment');
    }

    public function restore(AuthUser $authUser, KeuanganAdjustment $keuanganAdjustment): bool
    {
        return $authUser->can('Restore:KeuanganAdjustment');
    }

    public function forceDelete(AuthUser $authUser, KeuanganAdjustment $keuanganAdjustment): bool
    {
        return $authUser->can('ForceDelete:KeuanganAdjustment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:KeuanganAdjustment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:KeuanganAdjustment');
    }

    public function replicate(AuthUser $authUser, KeuanganAdjustment $keuanganAdjustment): bool
    {
        return $authUser->can('Replicate:KeuanganAdjustment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:KeuanganAdjustment');
    }

}