<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MasterMataKuliah;
use Illuminate\Auth\Access\HandlesAuthorization;

class MasterMataKuliahPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MasterMataKuliah');
    }

    public function view(AuthUser $authUser, MasterMataKuliah $masterMataKuliah): bool
    {
        return $authUser->can('View:MasterMataKuliah');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MasterMataKuliah');
    }

    public function update(AuthUser $authUser, MasterMataKuliah $masterMataKuliah): bool
    {
        return $authUser->can('Update:MasterMataKuliah');
    }

    public function delete(AuthUser $authUser, MasterMataKuliah $masterMataKuliah): bool
    {
        return $authUser->can('Delete:MasterMataKuliah');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MasterMataKuliah');
    }

    public function restore(AuthUser $authUser, MasterMataKuliah $masterMataKuliah): bool
    {
        return $authUser->can('Restore:MasterMataKuliah');
    }

    public function forceDelete(AuthUser $authUser, MasterMataKuliah $masterMataKuliah): bool
    {
        return $authUser->can('ForceDelete:MasterMataKuliah');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MasterMataKuliah');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MasterMataKuliah');
    }

    public function replicate(AuthUser $authUser, MasterMataKuliah $masterMataKuliah): bool
    {
        return $authUser->can('Replicate:MasterMataKuliah');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MasterMataKuliah');
    }

}