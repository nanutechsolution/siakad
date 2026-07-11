<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RefProgram;
use Illuminate\Auth\Access\HandlesAuthorization;

class RefProgramPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RefProgram');
    }

    public function view(AuthUser $authUser, RefProgram $refProgram): bool
    {
        return $authUser->can('View:RefProgram');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RefProgram');
    }

    public function update(AuthUser $authUser, RefProgram $refProgram): bool
    {
        return $authUser->can('Update:RefProgram');
    }

    public function delete(AuthUser $authUser, RefProgram $refProgram): bool
    {
        return $authUser->can('Delete:RefProgram');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RefProgram');
    }

    public function restore(AuthUser $authUser, RefProgram $refProgram): bool
    {
        return $authUser->can('Restore:RefProgram');
    }

    public function forceDelete(AuthUser $authUser, RefProgram $refProgram): bool
    {
        return $authUser->can('ForceDelete:RefProgram');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RefProgram');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RefProgram');
    }

    public function replicate(AuthUser $authUser, RefProgram $refProgram): bool
    {
        return $authUser->can('Replicate:RefProgram');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RefProgram');
    }

}