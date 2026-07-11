<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RefRuang;
use Illuminate\Auth\Access\HandlesAuthorization;

class RefRuangPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RefRuang');
    }

    public function view(AuthUser $authUser, RefRuang $refRuang): bool
    {
        return $authUser->can('View:RefRuang');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RefRuang');
    }

    public function update(AuthUser $authUser, RefRuang $refRuang): bool
    {
        return $authUser->can('Update:RefRuang');
    }

    public function delete(AuthUser $authUser, RefRuang $refRuang): bool
    {
        return $authUser->can('Delete:RefRuang');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RefRuang');
    }

    public function restore(AuthUser $authUser, RefRuang $refRuang): bool
    {
        return $authUser->can('Restore:RefRuang');
    }

    public function forceDelete(AuthUser $authUser, RefRuang $refRuang): bool
    {
        return $authUser->can('ForceDelete:RefRuang');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RefRuang');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RefRuang');
    }

    public function replicate(AuthUser $authUser, RefRuang $refRuang): bool
    {
        return $authUser->can('Replicate:RefRuang');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RefRuang');
    }

}