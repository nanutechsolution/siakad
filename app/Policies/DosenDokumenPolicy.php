<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DosenDokumen;
use Illuminate\Auth\Access\HandlesAuthorization;

class DosenDokumenPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DosenDokumen');
    }

    public function view(AuthUser $authUser, DosenDokumen $dosenDokumen): bool
    {
        return $authUser->can('View:DosenDokumen');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DosenDokumen');
    }

    public function update(AuthUser $authUser, DosenDokumen $dosenDokumen): bool
    {
        return $authUser->can('Update:DosenDokumen');
    }

    public function delete(AuthUser $authUser, DosenDokumen $dosenDokumen): bool
    {
        return $authUser->can('Delete:DosenDokumen');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:DosenDokumen');
    }

    public function restore(AuthUser $authUser, DosenDokumen $dosenDokumen): bool
    {
        return $authUser->can('Restore:DosenDokumen');
    }

    public function forceDelete(AuthUser $authUser, DosenDokumen $dosenDokumen): bool
    {
        return $authUser->can('ForceDelete:DosenDokumen');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DosenDokumen');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DosenDokumen');
    }

    public function replicate(AuthUser $authUser, DosenDokumen $dosenDokumen): bool
    {
        return $authUser->can('Replicate:DosenDokumen');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DosenDokumen');
    }

}