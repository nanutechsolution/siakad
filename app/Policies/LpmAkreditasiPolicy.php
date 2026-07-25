<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LpmAkreditasi;
use Illuminate\Auth\Access\HandlesAuthorization;

class LpmAkreditasiPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LpmAkreditasi');
    }

    public function view(AuthUser $authUser, LpmAkreditasi $lpmAkreditasi): bool
    {
        return $authUser->can('View:LpmAkreditasi');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LpmAkreditasi');
    }

    public function update(AuthUser $authUser, LpmAkreditasi $lpmAkreditasi): bool
    {
        return $authUser->can('Update:LpmAkreditasi');
    }

    public function delete(AuthUser $authUser, LpmAkreditasi $lpmAkreditasi): bool
    {
        return $authUser->can('Delete:LpmAkreditasi');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LpmAkreditasi');
    }

    public function restore(AuthUser $authUser, LpmAkreditasi $lpmAkreditasi): bool
    {
        return $authUser->can('Restore:LpmAkreditasi');
    }

    public function forceDelete(AuthUser $authUser, LpmAkreditasi $lpmAkreditasi): bool
    {
        return $authUser->can('ForceDelete:LpmAkreditasi');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LpmAkreditasi');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LpmAkreditasi');
    }

    public function replicate(AuthUser $authUser, LpmAkreditasi $lpmAkreditasi): bool
    {
        return $authUser->can('Replicate:LpmAkreditasi');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LpmAkreditasi');
    }

}