<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\JadwalKuliah;
use Illuminate\Auth\Access\HandlesAuthorization;

class JadwalKuliahPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:JadwalKuliah');
    }

    public function view(AuthUser $authUser, JadwalKuliah $jadwalKuliah): bool
    {
        return $authUser->can('View:JadwalKuliah');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:JadwalKuliah');
    }

    public function update(AuthUser $authUser, JadwalKuliah $jadwalKuliah): bool
    {
        return $authUser->can('Update:JadwalKuliah');
    }

    public function delete(AuthUser $authUser, JadwalKuliah $jadwalKuliah): bool
    {
        return $authUser->can('Delete:JadwalKuliah');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:JadwalKuliah');
    }

    public function restore(AuthUser $authUser, JadwalKuliah $jadwalKuliah): bool
    {
        return $authUser->can('Restore:JadwalKuliah');
    }

    public function forceDelete(AuthUser $authUser, JadwalKuliah $jadwalKuliah): bool
    {
        return $authUser->can('ForceDelete:JadwalKuliah');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:JadwalKuliah');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:JadwalKuliah');
    }

    public function replicate(AuthUser $authUser, JadwalKuliah $jadwalKuliah): bool
    {
        return $authUser->can('Replicate:JadwalKuliah');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:JadwalKuliah');
    }

}