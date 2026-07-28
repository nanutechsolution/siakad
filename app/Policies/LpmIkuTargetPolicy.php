<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LpmIkuTarget;
use Illuminate\Auth\Access\HandlesAuthorization;

class LpmIkuTargetPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LpmIkuTarget');
    }

    public function view(AuthUser $authUser, LpmIkuTarget $lpmIkuTarget): bool
    {
        return $authUser->can('View:LpmIkuTarget');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LpmIkuTarget');
    }

    public function update(AuthUser $authUser, LpmIkuTarget $lpmIkuTarget): bool
    {
        return $authUser->can('Update:LpmIkuTarget');
    }

    public function delete(AuthUser $authUser, LpmIkuTarget $lpmIkuTarget): bool
    {
        return $authUser->can('Delete:LpmIkuTarget');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LpmIkuTarget');
    }

    public function restore(AuthUser $authUser, LpmIkuTarget $lpmIkuTarget): bool
    {
        return $authUser->can('Restore:LpmIkuTarget');
    }

    public function forceDelete(AuthUser $authUser, LpmIkuTarget $lpmIkuTarget): bool
    {
        return $authUser->can('ForceDelete:LpmIkuTarget');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LpmIkuTarget');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LpmIkuTarget');
    }

    public function replicate(AuthUser $authUser, LpmIkuTarget $lpmIkuTarget): bool
    {
        return $authUser->can('Replicate:LpmIkuTarget');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LpmIkuTarget');
    }

}