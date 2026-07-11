<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\KeuanganMahasiswaBeasiswa;
use Illuminate\Auth\Access\HandlesAuthorization;

class KeuanganMahasiswaBeasiswaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:KeuanganMahasiswaBeasiswa');
    }

    public function view(AuthUser $authUser, KeuanganMahasiswaBeasiswa $keuanganMahasiswaBeasiswa): bool
    {
        return $authUser->can('View:KeuanganMahasiswaBeasiswa');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:KeuanganMahasiswaBeasiswa');
    }

    public function update(AuthUser $authUser, KeuanganMahasiswaBeasiswa $keuanganMahasiswaBeasiswa): bool
    {
        return $authUser->can('Update:KeuanganMahasiswaBeasiswa');
    }

    public function delete(AuthUser $authUser, KeuanganMahasiswaBeasiswa $keuanganMahasiswaBeasiswa): bool
    {
        return $authUser->can('Delete:KeuanganMahasiswaBeasiswa');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:KeuanganMahasiswaBeasiswa');
    }

    public function restore(AuthUser $authUser, KeuanganMahasiswaBeasiswa $keuanganMahasiswaBeasiswa): bool
    {
        return $authUser->can('Restore:KeuanganMahasiswaBeasiswa');
    }

    public function forceDelete(AuthUser $authUser, KeuanganMahasiswaBeasiswa $keuanganMahasiswaBeasiswa): bool
    {
        return $authUser->can('ForceDelete:KeuanganMahasiswaBeasiswa');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:KeuanganMahasiswaBeasiswa');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:KeuanganMahasiswaBeasiswa');
    }

    public function replicate(AuthUser $authUser, KeuanganMahasiswaBeasiswa $keuanganMahasiswaBeasiswa): bool
    {
        return $authUser->can('Replicate:KeuanganMahasiswaBeasiswa');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:KeuanganMahasiswaBeasiswa');
    }

}