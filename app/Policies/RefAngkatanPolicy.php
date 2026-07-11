<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RefAngkatan;
use Illuminate\Auth\Access\HandlesAuthorization;

class RefAngkatanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RefAngkatan');
    }

    public function view(AuthUser $authUser, RefAngkatan $refAngkatan): bool
    {
        return $authUser->can('View:RefAngkatan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RefAngkatan');
    }

    public function update(AuthUser $authUser, RefAngkatan $refAngkatan): bool
    {
        return $authUser->can('Update:RefAngkatan');
    }

    public function delete(AuthUser $authUser, RefAngkatan $refAngkatan): bool
    {
        return $authUser->can('Delete:RefAngkatan');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RefAngkatan');
    }

    public function restore(AuthUser $authUser, RefAngkatan $refAngkatan): bool
    {
        return $authUser->can('Restore:RefAngkatan');
    }

    public function forceDelete(AuthUser $authUser, RefAngkatan $refAngkatan): bool
    {
        return $authUser->can('ForceDelete:RefAngkatan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RefAngkatan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RefAngkatan');
    }

    public function replicate(AuthUser $authUser, RefAngkatan $refAngkatan): bool
    {
        return $authUser->can('Replicate:RefAngkatan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RefAngkatan');
    }

}