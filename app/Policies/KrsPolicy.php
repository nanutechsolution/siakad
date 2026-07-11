<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Krs;
use Illuminate\Auth\Access\HandlesAuthorization;

class KrsPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Krs');
    }

    public function view(AuthUser $authUser, Krs $krs): bool
    {
        return $authUser->can('View:Krs');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Krs');
    }

    public function update(AuthUser $authUser, Krs $krs): bool
    {
        return $authUser->can('Update:Krs');
    }

    public function delete(AuthUser $authUser, Krs $krs): bool
    {
        return $authUser->can('Delete:Krs');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Krs');
    }

    public function restore(AuthUser $authUser, Krs $krs): bool
    {
        return $authUser->can('Restore:Krs');
    }

    public function forceDelete(AuthUser $authUser, Krs $krs): bool
    {
        return $authUser->can('ForceDelete:Krs');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Krs');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Krs');
    }

    public function replicate(AuthUser $authUser, Krs $krs): bool
    {
        return $authUser->can('Replicate:Krs');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Krs');
    }

}