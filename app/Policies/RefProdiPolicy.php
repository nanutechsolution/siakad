<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RefProdi;
use Illuminate\Auth\Access\HandlesAuthorization;

class RefProdiPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RefProdi');
    }

    public function view(AuthUser $authUser, RefProdi $refProdi): bool
    {
        return $authUser->can('View:RefProdi');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RefProdi');
    }

    public function update(AuthUser $authUser, RefProdi $refProdi): bool
    {
        return $authUser->can('Update:RefProdi');
    }

    public function delete(AuthUser $authUser, RefProdi $refProdi): bool
    {
        return $authUser->can('Delete:RefProdi');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RefProdi');
    }

    public function restore(AuthUser $authUser, RefProdi $refProdi): bool
    {
        return $authUser->can('Restore:RefProdi');
    }

    public function forceDelete(AuthUser $authUser, RefProdi $refProdi): bool
    {
        return $authUser->can('ForceDelete:RefProdi');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RefProdi');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RefProdi');
    }

    public function replicate(AuthUser $authUser, RefProdi $refProdi): bool
    {
        return $authUser->can('Replicate:RefProdi');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RefProdi');
    }

}