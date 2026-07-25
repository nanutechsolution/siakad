<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LpmAkreditasiElemen;
use Illuminate\Auth\Access\HandlesAuthorization;

class LpmAkreditasiElemenPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LpmAkreditasiElemen');
    }

    public function view(AuthUser $authUser, LpmAkreditasiElemen $lpmAkreditasiElemen): bool
    {
        return $authUser->can('View:LpmAkreditasiElemen');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LpmAkreditasiElemen');
    }

    public function update(AuthUser $authUser, LpmAkreditasiElemen $lpmAkreditasiElemen): bool
    {
        return $authUser->can('Update:LpmAkreditasiElemen');
    }

    public function delete(AuthUser $authUser, LpmAkreditasiElemen $lpmAkreditasiElemen): bool
    {
        return $authUser->can('Delete:LpmAkreditasiElemen');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LpmAkreditasiElemen');
    }

    public function restore(AuthUser $authUser, LpmAkreditasiElemen $lpmAkreditasiElemen): bool
    {
        return $authUser->can('Restore:LpmAkreditasiElemen');
    }

    public function forceDelete(AuthUser $authUser, LpmAkreditasiElemen $lpmAkreditasiElemen): bool
    {
        return $authUser->can('ForceDelete:LpmAkreditasiElemen');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LpmAkreditasiElemen');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LpmAkreditasiElemen');
    }

    public function replicate(AuthUser $authUser, LpmAkreditasiElemen $lpmAkreditasiElemen): bool
    {
        return $authUser->can('Replicate:LpmAkreditasiElemen');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LpmAkreditasiElemen');
    }

}