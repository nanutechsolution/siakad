<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\KonfigurasiPembimbingAkademik;
use Illuminate\Auth\Access\HandlesAuthorization;

class KonfigurasiPembimbingAkademikPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:KonfigurasiPembimbingAkademik');
    }

    public function view(AuthUser $authUser, KonfigurasiPembimbingAkademik $konfigurasiPembimbingAkademik): bool
    {
        return $authUser->can('View:KonfigurasiPembimbingAkademik');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:KonfigurasiPembimbingAkademik');
    }

    public function update(AuthUser $authUser, KonfigurasiPembimbingAkademik $konfigurasiPembimbingAkademik): bool
    {
        return $authUser->can('Update:KonfigurasiPembimbingAkademik');
    }

    public function delete(AuthUser $authUser, KonfigurasiPembimbingAkademik $konfigurasiPembimbingAkademik): bool
    {
        return $authUser->can('Delete:KonfigurasiPembimbingAkademik');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:KonfigurasiPembimbingAkademik');
    }

    public function restore(AuthUser $authUser, KonfigurasiPembimbingAkademik $konfigurasiPembimbingAkademik): bool
    {
        return $authUser->can('Restore:KonfigurasiPembimbingAkademik');
    }

    public function forceDelete(AuthUser $authUser, KonfigurasiPembimbingAkademik $konfigurasiPembimbingAkademik): bool
    {
        return $authUser->can('ForceDelete:KonfigurasiPembimbingAkademik');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:KonfigurasiPembimbingAkademik');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:KonfigurasiPembimbingAkademik');
    }

    public function replicate(AuthUser $authUser, KonfigurasiPembimbingAkademik $konfigurasiPembimbingAkademik): bool
    {
        return $authUser->can('Replicate:KonfigurasiPembimbingAkademik');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:KonfigurasiPembimbingAkademik');
    }

}