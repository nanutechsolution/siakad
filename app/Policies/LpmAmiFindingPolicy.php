<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LpmAmiFinding;
use Illuminate\Auth\Access\HandlesAuthorization;

class LpmAmiFindingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LpmAmiFinding');
    }

    public function view(AuthUser $authUser, LpmAmiFinding $lpmAmiFinding): bool
    {
        return $authUser->can('View:LpmAmiFinding');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LpmAmiFinding');
    }

    public function update(AuthUser $authUser, LpmAmiFinding $lpmAmiFinding): bool
    {
        return $authUser->can('Update:LpmAmiFinding');
    }

    public function delete(AuthUser $authUser, LpmAmiFinding $lpmAmiFinding): bool
    {
        return $authUser->can('Delete:LpmAmiFinding');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LpmAmiFinding');
    }

    public function restore(AuthUser $authUser, LpmAmiFinding $lpmAmiFinding): bool
    {
        return $authUser->can('Restore:LpmAmiFinding');
    }

    public function forceDelete(AuthUser $authUser, LpmAmiFinding $lpmAmiFinding): bool
    {
        return $authUser->can('ForceDelete:LpmAmiFinding');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LpmAmiFinding');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LpmAmiFinding');
    }

    public function replicate(AuthUser $authUser, LpmAmiFinding $lpmAmiFinding): bool
    {
        return $authUser->can('Replicate:LpmAmiFinding');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LpmAmiFinding');
    }

}