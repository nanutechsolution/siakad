<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DispensasiAkademik;
use Illuminate\Auth\Access\HandlesAuthorization;

class DispensasiAkademikPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DispensasiAkademik');
    }

    public function view(AuthUser $authUser, DispensasiAkademik $dispensasiAkademik): bool
    {
        return $authUser->can('View:DispensasiAkademik');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DispensasiAkademik');
    }

    public function update(AuthUser $authUser, DispensasiAkademik $dispensasiAkademik): bool
    {
        return $authUser->can('Update:DispensasiAkademik');
    }

    public function delete(AuthUser $authUser, DispensasiAkademik $dispensasiAkademik): bool
    {
        return $authUser->can('Delete:DispensasiAkademik');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:DispensasiAkademik');
    }

    public function restore(AuthUser $authUser, DispensasiAkademik $dispensasiAkademik): bool
    {
        return $authUser->can('Restore:DispensasiAkademik');
    }

    public function forceDelete(AuthUser $authUser, DispensasiAkademik $dispensasiAkademik): bool
    {
        return $authUser->can('ForceDelete:DispensasiAkademik');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DispensasiAkademik');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DispensasiAkademik');
    }

    public function replicate(AuthUser $authUser, DispensasiAkademik $dispensasiAkademik): bool
    {
        return $authUser->can('Replicate:DispensasiAkademik');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DispensasiAkademik');
    }

}