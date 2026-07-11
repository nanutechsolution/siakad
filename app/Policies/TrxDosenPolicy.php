<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TrxDosen;
use Illuminate\Auth\Access\HandlesAuthorization;

class TrxDosenPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TrxDosen');
    }

    public function view(AuthUser $authUser, TrxDosen $trxDosen): bool
    {
        return $authUser->can('View:TrxDosen');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TrxDosen');
    }

    public function update(AuthUser $authUser, TrxDosen $trxDosen): bool
    {
        return $authUser->can('Update:TrxDosen');
    }

    public function delete(AuthUser $authUser, TrxDosen $trxDosen): bool
    {
        return $authUser->can('Delete:TrxDosen');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TrxDosen');
    }

    public function restore(AuthUser $authUser, TrxDosen $trxDosen): bool
    {
        return $authUser->can('Restore:TrxDosen');
    }

    public function forceDelete(AuthUser $authUser, TrxDosen $trxDosen): bool
    {
        return $authUser->can('ForceDelete:TrxDosen');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TrxDosen');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TrxDosen');
    }

    public function replicate(AuthUser $authUser, TrxDosen $trxDosen): bool
    {
        return $authUser->can('Replicate:TrxDosen');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TrxDosen');
    }

}