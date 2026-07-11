<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\KurikulumMataKuliah;
use Illuminate\Auth\Access\HandlesAuthorization;

class KurikulumMataKuliahPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:KurikulumMataKuliah');
    }

    public function view(AuthUser $authUser, KurikulumMataKuliah $kurikulumMataKuliah): bool
    {
        return $authUser->can('View:KurikulumMataKuliah');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:KurikulumMataKuliah');
    }

    public function update(AuthUser $authUser, KurikulumMataKuliah $kurikulumMataKuliah): bool
    {
        return $authUser->can('Update:KurikulumMataKuliah');
    }

    public function delete(AuthUser $authUser, KurikulumMataKuliah $kurikulumMataKuliah): bool
    {
        return $authUser->can('Delete:KurikulumMataKuliah');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:KurikulumMataKuliah');
    }

    public function restore(AuthUser $authUser, KurikulumMataKuliah $kurikulumMataKuliah): bool
    {
        return $authUser->can('Restore:KurikulumMataKuliah');
    }

    public function forceDelete(AuthUser $authUser, KurikulumMataKuliah $kurikulumMataKuliah): bool
    {
        return $authUser->can('ForceDelete:KurikulumMataKuliah');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:KurikulumMataKuliah');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:KurikulumMataKuliah');
    }

    public function replicate(AuthUser $authUser, KurikulumMataKuliah $kurikulumMataKuliah): bool
    {
        return $authUser->can('Replicate:KurikulumMataKuliah');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:KurikulumMataKuliah');
    }

}