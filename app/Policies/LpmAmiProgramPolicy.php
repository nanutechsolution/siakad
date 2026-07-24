<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LpmAmiProgram;
use Illuminate\Auth\Access\HandlesAuthorization;

class LpmAmiProgramPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LpmAmiProgram');
    }

    public function view(AuthUser $authUser, LpmAmiProgram $lpmAmiProgram): bool
    {
        return $authUser->can('View:LpmAmiProgram');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LpmAmiProgram');
    }

    public function update(AuthUser $authUser, LpmAmiProgram $lpmAmiProgram): bool
    {
        return $authUser->can('Update:LpmAmiProgram');
    }

    public function delete(AuthUser $authUser, LpmAmiProgram $lpmAmiProgram): bool
    {
        return $authUser->can('Delete:LpmAmiProgram');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LpmAmiProgram');
    }

    public function restore(AuthUser $authUser, LpmAmiProgram $lpmAmiProgram): bool
    {
        return $authUser->can('Restore:LpmAmiProgram');
    }

    public function forceDelete(AuthUser $authUser, LpmAmiProgram $lpmAmiProgram): bool
    {
        return $authUser->can('ForceDelete:LpmAmiProgram');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LpmAmiProgram');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LpmAmiProgram');
    }

    public function replicate(AuthUser $authUser, LpmAmiProgram $lpmAmiProgram): bool
    {
        return $authUser->can('Replicate:LpmAmiProgram');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LpmAmiProgram');
    }

}