<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\KeuanganMasterBeasiswa;
use Illuminate\Auth\Access\HandlesAuthorization;

class KeuanganMasterBeasiswaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:KeuanganMasterBeasiswa');
    }

    public function view(AuthUser $authUser, KeuanganMasterBeasiswa $keuanganMasterBeasiswa): bool
    {
        return $authUser->can('View:KeuanganMasterBeasiswa');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:KeuanganMasterBeasiswa');
    }

    public function update(AuthUser $authUser, KeuanganMasterBeasiswa $keuanganMasterBeasiswa): bool
    {
        return $authUser->can('Update:KeuanganMasterBeasiswa');
    }

    public function delete(AuthUser $authUser, KeuanganMasterBeasiswa $keuanganMasterBeasiswa): bool
    {
        return $authUser->can('Delete:KeuanganMasterBeasiswa');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:KeuanganMasterBeasiswa');
    }

    public function restore(AuthUser $authUser, KeuanganMasterBeasiswa $keuanganMasterBeasiswa): bool
    {
        return $authUser->can('Restore:KeuanganMasterBeasiswa');
    }

    public function forceDelete(AuthUser $authUser, KeuanganMasterBeasiswa $keuanganMasterBeasiswa): bool
    {
        return $authUser->can('ForceDelete:KeuanganMasterBeasiswa');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:KeuanganMasterBeasiswa');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:KeuanganMasterBeasiswa');
    }

    public function replicate(AuthUser $authUser, KeuanganMasterBeasiswa $keuanganMasterBeasiswa): bool
    {
        return $authUser->can('Replicate:KeuanganMasterBeasiswa');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:KeuanganMasterBeasiswa');
    }

}