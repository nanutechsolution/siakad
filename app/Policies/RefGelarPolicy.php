<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RefGelar;
use Illuminate\Auth\Access\HandlesAuthorization;

class RefGelarPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RefGelar');
    }

    public function view(AuthUser $authUser, RefGelar $refGelar): bool
    {
        return $authUser->can('View:RefGelar');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RefGelar');
    }

    public function update(AuthUser $authUser, RefGelar $refGelar): bool
    {
        return $authUser->can('Update:RefGelar');
    }

    public function delete(AuthUser $authUser, RefGelar $refGelar): bool
    {
        return $authUser->can('Delete:RefGelar');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RefGelar');
    }

    public function restore(AuthUser $authUser, RefGelar $refGelar): bool
    {
        return $authUser->can('Restore:RefGelar');
    }

    public function forceDelete(AuthUser $authUser, RefGelar $refGelar): bool
    {
        return $authUser->can('ForceDelete:RefGelar');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RefGelar');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RefGelar');
    }

    public function replicate(AuthUser $authUser, RefGelar $refGelar): bool
    {
        return $authUser->can('Replicate:RefGelar');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RefGelar');
    }

}