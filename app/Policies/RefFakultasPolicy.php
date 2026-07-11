<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RefFakultas;
use Illuminate\Auth\Access\HandlesAuthorization;

class RefFakultasPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RefFakultas');
    }

    public function view(AuthUser $authUser, RefFakultas $refFakultas): bool
    {
        return $authUser->can('View:RefFakultas');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RefFakultas');
    }

    public function update(AuthUser $authUser, RefFakultas $refFakultas): bool
    {
        return $authUser->can('Update:RefFakultas');
    }

    public function delete(AuthUser $authUser, RefFakultas $refFakultas): bool
    {
        return $authUser->can('Delete:RefFakultas');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RefFakultas');
    }

    public function restore(AuthUser $authUser, RefFakultas $refFakultas): bool
    {
        return $authUser->can('Restore:RefFakultas');
    }

    public function forceDelete(AuthUser $authUser, RefFakultas $refFakultas): bool
    {
        return $authUser->can('ForceDelete:RefFakultas');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RefFakultas');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RefFakultas');
    }

    public function replicate(AuthUser $authUser, RefFakultas $refFakultas): bool
    {
        return $authUser->can('Replicate:RefFakultas');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RefFakultas');
    }

}