<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ProfileChangeRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProfileChangeRequestPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProfileChangeRequest');
    }

    public function view(AuthUser $authUser, ProfileChangeRequest $profileChangeRequest): bool
    {
        return $authUser->can('View:ProfileChangeRequest');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProfileChangeRequest');
    }

    public function update(AuthUser $authUser, ProfileChangeRequest $profileChangeRequest): bool
    {
        return $authUser->can('Update:ProfileChangeRequest');
    }

    public function delete(AuthUser $authUser, ProfileChangeRequest $profileChangeRequest): bool
    {
        return $authUser->can('Delete:ProfileChangeRequest');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ProfileChangeRequest');
    }

    public function restore(AuthUser $authUser, ProfileChangeRequest $profileChangeRequest): bool
    {
        return $authUser->can('Restore:ProfileChangeRequest');
    }

    public function forceDelete(AuthUser $authUser, ProfileChangeRequest $profileChangeRequest): bool
    {
        return $authUser->can('ForceDelete:ProfileChangeRequest');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ProfileChangeRequest');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ProfileChangeRequest');
    }

    public function replicate(AuthUser $authUser, ProfileChangeRequest $profileChangeRequest): bool
    {
        return $authUser->can('Replicate:ProfileChangeRequest');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ProfileChangeRequest');
    }

}