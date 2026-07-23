<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LpmKuisionerKelompok;
use Illuminate\Auth\Access\HandlesAuthorization;

class LpmKuisionerKelompokPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LpmKuisionerKelompok');
    }

    public function view(AuthUser $authUser, LpmKuisionerKelompok $lpmKuisionerKelompok): bool
    {
        return $authUser->can('View:LpmKuisionerKelompok');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LpmKuisionerKelompok');
    }

    public function update(AuthUser $authUser, LpmKuisionerKelompok $lpmKuisionerKelompok): bool
    {
        return $authUser->can('Update:LpmKuisionerKelompok');
    }

    public function delete(AuthUser $authUser, LpmKuisionerKelompok $lpmKuisionerKelompok): bool
    {
        return $authUser->can('Delete:LpmKuisionerKelompok');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LpmKuisionerKelompok');
    }

    public function restore(AuthUser $authUser, LpmKuisionerKelompok $lpmKuisionerKelompok): bool
    {
        return $authUser->can('Restore:LpmKuisionerKelompok');
    }

    public function forceDelete(AuthUser $authUser, LpmKuisionerKelompok $lpmKuisionerKelompok): bool
    {
        return $authUser->can('ForceDelete:LpmKuisionerKelompok');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LpmKuisionerKelompok');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LpmKuisionerKelompok');
    }

    public function replicate(AuthUser $authUser, LpmKuisionerKelompok $lpmKuisionerKelompok): bool
    {
        return $authUser->can('Replicate:LpmKuisionerKelompok');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LpmKuisionerKelompok');
    }

}