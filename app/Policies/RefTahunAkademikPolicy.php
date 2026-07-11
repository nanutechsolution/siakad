<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RefTahunAkademik;
use Illuminate\Auth\Access\HandlesAuthorization;

class RefTahunAkademikPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RefTahunAkademik');
    }

    public function view(AuthUser $authUser, RefTahunAkademik $refTahunAkademik): bool
    {
        return $authUser->can('View:RefTahunAkademik');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RefTahunAkademik');
    }

    public function update(AuthUser $authUser, RefTahunAkademik $refTahunAkademik): bool
    {
        return $authUser->can('Update:RefTahunAkademik');
    }

    public function delete(AuthUser $authUser, RefTahunAkademik $refTahunAkademik): bool
    {
        return $authUser->can('Delete:RefTahunAkademik');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RefTahunAkademik');
    }

    public function restore(AuthUser $authUser, RefTahunAkademik $refTahunAkademik): bool
    {
        return $authUser->can('Restore:RefTahunAkademik');
    }

    public function forceDelete(AuthUser $authUser, RefTahunAkademik $refTahunAkademik): bool
    {
        return $authUser->can('ForceDelete:RefTahunAkademik');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RefTahunAkademik');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RefTahunAkademik');
    }

    public function replicate(AuthUser $authUser, RefTahunAkademik $refTahunAkademik): bool
    {
        return $authUser->can('Replicate:RefTahunAkademik');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RefTahunAkademik');
    }

}