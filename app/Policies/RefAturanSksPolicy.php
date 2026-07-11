<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RefAturanSks;
use Illuminate\Auth\Access\HandlesAuthorization;

class RefAturanSksPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RefAturanSks');
    }

    public function view(AuthUser $authUser, RefAturanSks $refAturanSks): bool
    {
        return $authUser->can('View:RefAturanSks');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RefAturanSks');
    }

    public function update(AuthUser $authUser, RefAturanSks $refAturanSks): bool
    {
        return $authUser->can('Update:RefAturanSks');
    }

    public function delete(AuthUser $authUser, RefAturanSks $refAturanSks): bool
    {
        return $authUser->can('Delete:RefAturanSks');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RefAturanSks');
    }

    public function restore(AuthUser $authUser, RefAturanSks $refAturanSks): bool
    {
        return $authUser->can('Restore:RefAturanSks');
    }

    public function forceDelete(AuthUser $authUser, RefAturanSks $refAturanSks): bool
    {
        return $authUser->can('ForceDelete:RefAturanSks');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RefAturanSks');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RefAturanSks');
    }

    public function replicate(AuthUser $authUser, RefAturanSks $refAturanSks): bool
    {
        return $authUser->can('Replicate:RefAturanSks');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RefAturanSks');
    }

}