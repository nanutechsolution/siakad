<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RefPersonRole;
use Illuminate\Auth\Access\HandlesAuthorization;

class RefPersonRolePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RefPersonRole');
    }

    public function view(AuthUser $authUser, RefPersonRole $refPersonRole): bool
    {
        return $authUser->can('View:RefPersonRole');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RefPersonRole');
    }

    public function update(AuthUser $authUser, RefPersonRole $refPersonRole): bool
    {
        return $authUser->can('Update:RefPersonRole');
    }

    public function delete(AuthUser $authUser, RefPersonRole $refPersonRole): bool
    {
        return $authUser->can('Delete:RefPersonRole');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RefPersonRole');
    }

    public function restore(AuthUser $authUser, RefPersonRole $refPersonRole): bool
    {
        return $authUser->can('Restore:RefPersonRole');
    }

    public function forceDelete(AuthUser $authUser, RefPersonRole $refPersonRole): bool
    {
        return $authUser->can('ForceDelete:RefPersonRole');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RefPersonRole');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RefPersonRole');
    }

    public function replicate(AuthUser $authUser, RefPersonRole $refPersonRole): bool
    {
        return $authUser->can('Replicate:RefPersonRole');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RefPersonRole');
    }

}