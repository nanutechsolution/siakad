<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DosenKetersediaan;
use Illuminate\Auth\Access\HandlesAuthorization;

class DosenKetersediaanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DosenKetersediaan');
    }

    public function view(AuthUser $authUser, DosenKetersediaan $dosenKetersediaan): bool
    {
        return $authUser->can('View:DosenKetersediaan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DosenKetersediaan');
    }

    public function update(AuthUser $authUser, DosenKetersediaan $dosenKetersediaan): bool
    {
        return $authUser->can('Update:DosenKetersediaan');
    }

    public function delete(AuthUser $authUser, DosenKetersediaan $dosenKetersediaan): bool
    {
        return $authUser->can('Delete:DosenKetersediaan');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:DosenKetersediaan');
    }

    public function restore(AuthUser $authUser, DosenKetersediaan $dosenKetersediaan): bool
    {
        return $authUser->can('Restore:DosenKetersediaan');
    }

    public function forceDelete(AuthUser $authUser, DosenKetersediaan $dosenKetersediaan): bool
    {
        return $authUser->can('ForceDelete:DosenKetersediaan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DosenKetersediaan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DosenKetersediaan');
    }

    public function replicate(AuthUser $authUser, DosenKetersediaan $dosenKetersediaan): bool
    {
        return $authUser->can('Replicate:DosenKetersediaan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DosenKetersediaan');
    }

}