<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TrxPegawai;
use Illuminate\Auth\Access\HandlesAuthorization;

class TrxPegawaiPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TrxPegawai');
    }

    public function view(AuthUser $authUser, TrxPegawai $trxPegawai): bool
    {
        return $authUser->can('View:TrxPegawai');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TrxPegawai');
    }

    public function update(AuthUser $authUser, TrxPegawai $trxPegawai): bool
    {
        return $authUser->can('Update:TrxPegawai');
    }

    public function delete(AuthUser $authUser, TrxPegawai $trxPegawai): bool
    {
        return $authUser->can('Delete:TrxPegawai');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TrxPegawai');
    }

    public function restore(AuthUser $authUser, TrxPegawai $trxPegawai): bool
    {
        return $authUser->can('Restore:TrxPegawai');
    }

    public function forceDelete(AuthUser $authUser, TrxPegawai $trxPegawai): bool
    {
        return $authUser->can('ForceDelete:TrxPegawai');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TrxPegawai');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TrxPegawai');
    }

    public function replicate(AuthUser $authUser, TrxPegawai $trxPegawai): bool
    {
        return $authUser->can('Replicate:TrxPegawai');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TrxPegawai');
    }

}