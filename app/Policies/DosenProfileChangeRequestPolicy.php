<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DosenProfileChangeRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class DosenProfileChangeRequestPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DosenProfileChangeRequest');
    }

    public function view(AuthUser $authUser, DosenProfileChangeRequest $dosenProfileChangeRequest): bool
    {
        return $authUser->can('View:DosenProfileChangeRequest');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DosenProfileChangeRequest');
    }

    public function update(AuthUser $authUser, DosenProfileChangeRequest $dosenProfileChangeRequest): bool
    {
        return $authUser->can('Update:DosenProfileChangeRequest');
    }

    public function delete(AuthUser $authUser, DosenProfileChangeRequest $dosenProfileChangeRequest): bool
    {
        return $authUser->can('Delete:DosenProfileChangeRequest');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:DosenProfileChangeRequest');
    }

    public function restore(AuthUser $authUser, DosenProfileChangeRequest $dosenProfileChangeRequest): bool
    {
        return $authUser->can('Restore:DosenProfileChangeRequest');
    }

    public function forceDelete(AuthUser $authUser, DosenProfileChangeRequest $dosenProfileChangeRequest): bool
    {
        return $authUser->can('ForceDelete:DosenProfileChangeRequest');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DosenProfileChangeRequest');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DosenProfileChangeRequest');
    }

    public function replicate(AuthUser $authUser, DosenProfileChangeRequest $dosenProfileChangeRequest): bool
    {
        return $authUser->can('Replicate:DosenProfileChangeRequest');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DosenProfileChangeRequest');
    }

}