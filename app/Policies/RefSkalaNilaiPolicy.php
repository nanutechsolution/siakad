<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RefSkalaNilai;
use Illuminate\Auth\Access\HandlesAuthorization;

class RefSkalaNilaiPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RefSkalaNilai');
    }

    public function view(AuthUser $authUser, RefSkalaNilai $refSkalaNilai): bool
    {
        return $authUser->can('View:RefSkalaNilai');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RefSkalaNilai');
    }

    public function update(AuthUser $authUser, RefSkalaNilai $refSkalaNilai): bool
    {
        return $authUser->can('Update:RefSkalaNilai');
    }

    public function delete(AuthUser $authUser, RefSkalaNilai $refSkalaNilai): bool
    {
        return $authUser->can('Delete:RefSkalaNilai');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RefSkalaNilai');
    }

    public function restore(AuthUser $authUser, RefSkalaNilai $refSkalaNilai): bool
    {
        return $authUser->can('Restore:RefSkalaNilai');
    }

    public function forceDelete(AuthUser $authUser, RefSkalaNilai $refSkalaNilai): bool
    {
        return $authUser->can('ForceDelete:RefSkalaNilai');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RefSkalaNilai');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RefSkalaNilai');
    }

    public function replicate(AuthUser $authUser, RefSkalaNilai $refSkalaNilai): bool
    {
        return $authUser->can('Replicate:RefSkalaNilai');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RefSkalaNilai');
    }

}