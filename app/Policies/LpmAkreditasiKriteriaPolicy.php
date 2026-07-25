<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LpmAkreditasiKriteria;
use Illuminate\Auth\Access\HandlesAuthorization;

class LpmAkreditasiKriteriaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LpmAkreditasiKriteria');
    }

    public function view(AuthUser $authUser, LpmAkreditasiKriteria $lpmAkreditasiKriteria): bool
    {
        return $authUser->can('View:LpmAkreditasiKriteria');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LpmAkreditasiKriteria');
    }

    public function update(AuthUser $authUser, LpmAkreditasiKriteria $lpmAkreditasiKriteria): bool
    {
        return $authUser->can('Update:LpmAkreditasiKriteria');
    }

    public function delete(AuthUser $authUser, LpmAkreditasiKriteria $lpmAkreditasiKriteria): bool
    {
        return $authUser->can('Delete:LpmAkreditasiKriteria');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LpmAkreditasiKriteria');
    }

    public function restore(AuthUser $authUser, LpmAkreditasiKriteria $lpmAkreditasiKriteria): bool
    {
        return $authUser->can('Restore:LpmAkreditasiKriteria');
    }

    public function forceDelete(AuthUser $authUser, LpmAkreditasiKriteria $lpmAkreditasiKriteria): bool
    {
        return $authUser->can('ForceDelete:LpmAkreditasiKriteria');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LpmAkreditasiKriteria');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LpmAkreditasiKriteria');
    }

    public function replicate(AuthUser $authUser, LpmAkreditasiKriteria $lpmAkreditasiKriteria): bool
    {
        return $authUser->can('Replicate:LpmAkreditasiKriteria');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LpmAkreditasiKriteria');
    }

}