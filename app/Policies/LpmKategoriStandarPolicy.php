<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LpmKategoriStandar;
use Illuminate\Auth\Access\HandlesAuthorization;

class LpmKategoriStandarPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LpmKategoriStandar');
    }

    public function view(AuthUser $authUser, LpmKategoriStandar $lpmKategoriStandar): bool
    {
        return $authUser->can('View:LpmKategoriStandar');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LpmKategoriStandar');
    }

    public function update(AuthUser $authUser, LpmKategoriStandar $lpmKategoriStandar): bool
    {
        return $authUser->can('Update:LpmKategoriStandar');
    }

    public function delete(AuthUser $authUser, LpmKategoriStandar $lpmKategoriStandar): bool
    {
        return $authUser->can('Delete:LpmKategoriStandar');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LpmKategoriStandar');
    }

    public function restore(AuthUser $authUser, LpmKategoriStandar $lpmKategoriStandar): bool
    {
        return $authUser->can('Restore:LpmKategoriStandar');
    }

    public function forceDelete(AuthUser $authUser, LpmKategoriStandar $lpmKategoriStandar): bool
    {
        return $authUser->can('ForceDelete:LpmKategoriStandar');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LpmKategoriStandar');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LpmKategoriStandar');
    }

    public function replicate(AuthUser $authUser, LpmKategoriStandar $lpmKategoriStandar): bool
    {
        return $authUser->can('Replicate:LpmKategoriStandar');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LpmKategoriStandar');
    }

}