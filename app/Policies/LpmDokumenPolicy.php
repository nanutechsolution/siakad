<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LpmDokumen;
use Illuminate\Auth\Access\HandlesAuthorization;

class LpmDokumenPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LpmDokumen');
    }

    public function view(AuthUser $authUser, LpmDokumen $lpmDokumen): bool
    {
        return $authUser->can('View:LpmDokumen');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LpmDokumen');
    }

    public function update(AuthUser $authUser, LpmDokumen $lpmDokumen): bool
    {
        return $authUser->can('Update:LpmDokumen');
    }

    public function delete(AuthUser $authUser, LpmDokumen $lpmDokumen): bool
    {
        return $authUser->can('Delete:LpmDokumen');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LpmDokumen');
    }

    public function restore(AuthUser $authUser, LpmDokumen $lpmDokumen): bool
    {
        return $authUser->can('Restore:LpmDokumen');
    }

    public function forceDelete(AuthUser $authUser, LpmDokumen $lpmDokumen): bool
    {
        return $authUser->can('ForceDelete:LpmDokumen');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LpmDokumen');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LpmDokumen');
    }

    public function replicate(AuthUser $authUser, LpmDokumen $lpmDokumen): bool
    {
        return $authUser->can('Replicate:LpmDokumen');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LpmDokumen');
    }

}