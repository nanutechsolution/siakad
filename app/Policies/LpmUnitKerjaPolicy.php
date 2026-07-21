<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LpmUnitKerja;
use Illuminate\Auth\Access\HandlesAuthorization;

class LpmUnitKerjaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LpmUnitKerja');
    }

    public function view(AuthUser $authUser, LpmUnitKerja $lpmUnitKerja): bool
    {
        return $authUser->can('View:LpmUnitKerja');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LpmUnitKerja');
    }

    public function update(AuthUser $authUser, LpmUnitKerja $lpmUnitKerja): bool
    {
        return $authUser->can('Update:LpmUnitKerja');
    }

    public function delete(AuthUser $authUser, LpmUnitKerja $lpmUnitKerja): bool
    {
        return $authUser->can('Delete:LpmUnitKerja');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LpmUnitKerja');
    }

    public function restore(AuthUser $authUser, LpmUnitKerja $lpmUnitKerja): bool
    {
        return $authUser->can('Restore:LpmUnitKerja');
    }

    public function forceDelete(AuthUser $authUser, LpmUnitKerja $lpmUnitKerja): bool
    {
        return $authUser->can('ForceDelete:LpmUnitKerja');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LpmUnitKerja');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LpmUnitKerja');
    }

    public function replicate(AuthUser $authUser, LpmUnitKerja $lpmUnitKerja): bool
    {
        return $authUser->can('Replicate:LpmUnitKerja');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LpmUnitKerja');
    }

}