<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LpmAkreditasiLembaga;
use Illuminate\Auth\Access\HandlesAuthorization;

class LpmAkreditasiLembagaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LpmAkreditasiLembaga');
    }

    public function view(AuthUser $authUser, LpmAkreditasiLembaga $lpmAkreditasiLembaga): bool
    {
        return $authUser->can('View:LpmAkreditasiLembaga');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LpmAkreditasiLembaga');
    }

    public function update(AuthUser $authUser, LpmAkreditasiLembaga $lpmAkreditasiLembaga): bool
    {
        return $authUser->can('Update:LpmAkreditasiLembaga');
    }

    public function delete(AuthUser $authUser, LpmAkreditasiLembaga $lpmAkreditasiLembaga): bool
    {
        return $authUser->can('Delete:LpmAkreditasiLembaga');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LpmAkreditasiLembaga');
    }

    public function restore(AuthUser $authUser, LpmAkreditasiLembaga $lpmAkreditasiLembaga): bool
    {
        return $authUser->can('Restore:LpmAkreditasiLembaga');
    }

    public function forceDelete(AuthUser $authUser, LpmAkreditasiLembaga $lpmAkreditasiLembaga): bool
    {
        return $authUser->can('ForceDelete:LpmAkreditasiLembaga');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LpmAkreditasiLembaga');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LpmAkreditasiLembaga');
    }

    public function replicate(AuthUser $authUser, LpmAkreditasiLembaga $lpmAkreditasiLembaga): bool
    {
        return $authUser->can('Replicate:LpmAkreditasiLembaga');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LpmAkreditasiLembaga');
    }

}