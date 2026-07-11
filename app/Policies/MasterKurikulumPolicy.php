<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MasterKurikulum;
use Illuminate\Auth\Access\HandlesAuthorization;

class MasterKurikulumPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MasterKurikulum');
    }

    public function view(AuthUser $authUser, MasterKurikulum $masterKurikulum): bool
    {
        return $authUser->can('View:MasterKurikulum');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MasterKurikulum');
    }

    public function update(AuthUser $authUser, MasterKurikulum $masterKurikulum): bool
    {
        return $authUser->can('Update:MasterKurikulum');
    }

    public function delete(AuthUser $authUser, MasterKurikulum $masterKurikulum): bool
    {
        return $authUser->can('Delete:MasterKurikulum');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MasterKurikulum');
    }

    public function restore(AuthUser $authUser, MasterKurikulum $masterKurikulum): bool
    {
        return $authUser->can('Restore:MasterKurikulum');
    }

    public function forceDelete(AuthUser $authUser, MasterKurikulum $masterKurikulum): bool
    {
        return $authUser->can('ForceDelete:MasterKurikulum');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MasterKurikulum');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MasterKurikulum');
    }

    public function replicate(AuthUser $authUser, MasterKurikulum $masterKurikulum): bool
    {
        return $authUser->can('Replicate:MasterKurikulum');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MasterKurikulum');
    }

}