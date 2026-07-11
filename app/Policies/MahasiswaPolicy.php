<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Mahasiswa;
use Illuminate\Auth\Access\HandlesAuthorization;

class MahasiswaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Mahasiswa');
    }

    public function view(AuthUser $authUser, Mahasiswa $mahasiswa): bool
    {
        return $authUser->can('View:Mahasiswa');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Mahasiswa');
    }

    public function update(AuthUser $authUser, Mahasiswa $mahasiswa): bool
    {
        return $authUser->can('Update:Mahasiswa');
    }

    public function delete(AuthUser $authUser, Mahasiswa $mahasiswa): bool
    {
        return $authUser->can('Delete:Mahasiswa');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Mahasiswa');
    }

    public function restore(AuthUser $authUser, Mahasiswa $mahasiswa): bool
    {
        return $authUser->can('Restore:Mahasiswa');
    }

    public function forceDelete(AuthUser $authUser, Mahasiswa $mahasiswa): bool
    {
        return $authUser->can('ForceDelete:Mahasiswa');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Mahasiswa');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Mahasiswa');
    }

    public function replicate(AuthUser $authUser, Mahasiswa $mahasiswa): bool
    {
        return $authUser->can('Replicate:Mahasiswa');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Mahasiswa');
    }

}