<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\KeuanganKomponenBiaya;
use Illuminate\Auth\Access\HandlesAuthorization;

class KeuanganKomponenBiayaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:KeuanganKomponenBiaya');
    }

    public function view(AuthUser $authUser, KeuanganKomponenBiaya $keuanganKomponenBiaya): bool
    {
        return $authUser->can('View:KeuanganKomponenBiaya');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:KeuanganKomponenBiaya');
    }

    public function update(AuthUser $authUser, KeuanganKomponenBiaya $keuanganKomponenBiaya): bool
    {
        return $authUser->can('Update:KeuanganKomponenBiaya');
    }

    public function delete(AuthUser $authUser, KeuanganKomponenBiaya $keuanganKomponenBiaya): bool
    {
        return $authUser->can('Delete:KeuanganKomponenBiaya');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:KeuanganKomponenBiaya');
    }

    public function restore(AuthUser $authUser, KeuanganKomponenBiaya $keuanganKomponenBiaya): bool
    {
        return $authUser->can('Restore:KeuanganKomponenBiaya');
    }

    public function forceDelete(AuthUser $authUser, KeuanganKomponenBiaya $keuanganKomponenBiaya): bool
    {
        return $authUser->can('ForceDelete:KeuanganKomponenBiaya');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:KeuanganKomponenBiaya');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:KeuanganKomponenBiaya');
    }

    public function replicate(AuthUser $authUser, KeuanganKomponenBiaya $keuanganKomponenBiaya): bool
    {
        return $authUser->can('Replicate:KeuanganKomponenBiaya');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:KeuanganKomponenBiaya');
    }

}