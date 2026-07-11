<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RefJabatan;
use Illuminate\Auth\Access\HandlesAuthorization;

class RefJabatanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RefJabatan');
    }

    public function view(AuthUser $authUser, RefJabatan $refJabatan): bool
    {
        return $authUser->can('View:RefJabatan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RefJabatan');
    }

    public function update(AuthUser $authUser, RefJabatan $refJabatan): bool
    {
        return $authUser->can('Update:RefJabatan');
    }

    public function delete(AuthUser $authUser, RefJabatan $refJabatan): bool
    {
        return $authUser->can('Delete:RefJabatan');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RefJabatan');
    }

    public function restore(AuthUser $authUser, RefJabatan $refJabatan): bool
    {
        return $authUser->can('Restore:RefJabatan');
    }

    public function forceDelete(AuthUser $authUser, RefJabatan $refJabatan): bool
    {
        return $authUser->can('ForceDelete:RefJabatan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RefJabatan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RefJabatan');
    }

    public function replicate(AuthUser $authUser, RefJabatan $refJabatan): bool
    {
        return $authUser->can('Replicate:RefJabatan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RefJabatan');
    }

}