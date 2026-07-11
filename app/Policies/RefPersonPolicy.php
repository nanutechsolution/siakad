<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RefPerson;
use Illuminate\Auth\Access\HandlesAuthorization;

class RefPersonPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RefPerson');
    }

    public function view(AuthUser $authUser, RefPerson $refPerson): bool
    {
        return $authUser->can('View:RefPerson');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RefPerson');
    }

    public function update(AuthUser $authUser, RefPerson $refPerson): bool
    {
        return $authUser->can('Update:RefPerson');
    }

    public function delete(AuthUser $authUser, RefPerson $refPerson): bool
    {
        return $authUser->can('Delete:RefPerson');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RefPerson');
    }

    public function restore(AuthUser $authUser, RefPerson $refPerson): bool
    {
        return $authUser->can('Restore:RefPerson');
    }

    public function forceDelete(AuthUser $authUser, RefPerson $refPerson): bool
    {
        return $authUser->can('ForceDelete:RefPerson');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RefPerson');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RefPerson');
    }

    public function replicate(AuthUser $authUser, RefPerson $refPerson): bool
    {
        return $authUser->can('Replicate:RefPerson');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RefPerson');
    }

}