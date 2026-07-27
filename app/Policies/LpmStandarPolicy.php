<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LpmStandar;
use Illuminate\Auth\Access\HandlesAuthorization;

class LpmStandarPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LpmStandar');
    }

    public function view(AuthUser $authUser, LpmStandar $lpmStandar): bool
    {
        return $authUser->can('View:LpmStandar');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LpmStandar');
    }

    public function update(AuthUser $authUser, LpmStandar $lpmStandar): bool
    {
        return $authUser->can('Update:LpmStandar');
    }

    public function delete(AuthUser $authUser, LpmStandar $lpmStandar): bool
    {
        return $authUser->can('Delete:LpmStandar');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LpmStandar');
    }

    public function restore(AuthUser $authUser, LpmStandar $lpmStandar): bool
    {
        return $authUser->can('Restore:LpmStandar');
    }

    public function forceDelete(AuthUser $authUser, LpmStandar $lpmStandar): bool
    {
        return $authUser->can('ForceDelete:LpmStandar');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LpmStandar');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LpmStandar');
    }

    public function replicate(AuthUser $authUser, LpmStandar $lpmStandar): bool
    {
        return $authUser->can('Replicate:LpmStandar');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LpmStandar');
    }

}