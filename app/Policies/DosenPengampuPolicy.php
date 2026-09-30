<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DosenPengampu;
use Illuminate\Auth\Access\HandlesAuthorization;

class DosenPengampuPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DosenPengampu');
    }

    public function view(AuthUser $authUser, DosenPengampu $dosenPengampu): bool
    {
        return $authUser->can('View:DosenPengampu');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DosenPengampu');
    }

    public function update(AuthUser $authUser, DosenPengampu $dosenPengampu): bool
    {
        return $authUser->can('Update:DosenPengampu');
    }

    public function delete(AuthUser $authUser, DosenPengampu $dosenPengampu): bool
    {
        return $authUser->can('Delete:DosenPengampu');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:DosenPengampu');
    }

    public function restore(AuthUser $authUser, DosenPengampu $dosenPengampu): bool
    {
        return $authUser->can('Restore:DosenPengampu');
    }

    public function forceDelete(AuthUser $authUser, DosenPengampu $dosenPengampu): bool
    {
        return $authUser->can('ForceDelete:DosenPengampu');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DosenPengampu');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DosenPengampu');
    }

    public function replicate(AuthUser $authUser, DosenPengampu $dosenPengampu): bool
    {
        return $authUser->can('Replicate:DosenPengampu');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DosenPengampu');
    }

}