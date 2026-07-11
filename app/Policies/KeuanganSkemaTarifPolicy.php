<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\KeuanganSkemaTarif;
use Illuminate\Auth\Access\HandlesAuthorization;

class KeuanganSkemaTarifPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:KeuanganSkemaTarif');
    }

    public function view(AuthUser $authUser, KeuanganSkemaTarif $keuanganSkemaTarif): bool
    {
        return $authUser->can('View:KeuanganSkemaTarif');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:KeuanganSkemaTarif');
    }

    public function update(AuthUser $authUser, KeuanganSkemaTarif $keuanganSkemaTarif): bool
    {
        return $authUser->can('Update:KeuanganSkemaTarif');
    }

    public function delete(AuthUser $authUser, KeuanganSkemaTarif $keuanganSkemaTarif): bool
    {
        return $authUser->can('Delete:KeuanganSkemaTarif');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:KeuanganSkemaTarif');
    }

    public function restore(AuthUser $authUser, KeuanganSkemaTarif $keuanganSkemaTarif): bool
    {
        return $authUser->can('Restore:KeuanganSkemaTarif');
    }

    public function forceDelete(AuthUser $authUser, KeuanganSkemaTarif $keuanganSkemaTarif): bool
    {
        return $authUser->can('ForceDelete:KeuanganSkemaTarif');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:KeuanganSkemaTarif');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:KeuanganSkemaTarif');
    }

    public function replicate(AuthUser $authUser, KeuanganSkemaTarif $keuanganSkemaTarif): bool
    {
        return $authUser->can('Replicate:KeuanganSkemaTarif');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:KeuanganSkemaTarif');
    }

}