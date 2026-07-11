<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PembayaranMahasiswa;
use Illuminate\Auth\Access\HandlesAuthorization;

class PembayaranMahasiswaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PembayaranMahasiswa');
    }

    public function view(AuthUser $authUser, PembayaranMahasiswa $pembayaranMahasiswa): bool
    {
        return $authUser->can('View:PembayaranMahasiswa');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PembayaranMahasiswa');
    }

    public function update(AuthUser $authUser, PembayaranMahasiswa $pembayaranMahasiswa): bool
    {
        return $authUser->can('Update:PembayaranMahasiswa');
    }

    public function delete(AuthUser $authUser, PembayaranMahasiswa $pembayaranMahasiswa): bool
    {
        return $authUser->can('Delete:PembayaranMahasiswa');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PembayaranMahasiswa');
    }

    public function restore(AuthUser $authUser, PembayaranMahasiswa $pembayaranMahasiswa): bool
    {
        return $authUser->can('Restore:PembayaranMahasiswa');
    }

    public function forceDelete(AuthUser $authUser, PembayaranMahasiswa $pembayaranMahasiswa): bool
    {
        return $authUser->can('ForceDelete:PembayaranMahasiswa');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PembayaranMahasiswa');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PembayaranMahasiswa');
    }

    public function replicate(AuthUser $authUser, PembayaranMahasiswa $pembayaranMahasiswa): bool
    {
        return $authUser->can('Replicate:PembayaranMahasiswa');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PembayaranMahasiswa');
    }

}