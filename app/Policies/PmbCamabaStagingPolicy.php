<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PmbCamabaStaging;
use Illuminate\Auth\Access\HandlesAuthorization;

class PmbCamabaStagingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PmbCamabaStaging');
    }

    public function view(AuthUser $authUser, PmbCamabaStaging $pmbCamabaStaging): bool
    {
        return $authUser->can('View:PmbCamabaStaging');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PmbCamabaStaging');
    }

    public function update(AuthUser $authUser, PmbCamabaStaging $pmbCamabaStaging): bool
    {
        return $authUser->can('Update:PmbCamabaStaging');
    }

    public function delete(AuthUser $authUser, PmbCamabaStaging $pmbCamabaStaging): bool
    {
        return $authUser->can('Delete:PmbCamabaStaging');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PmbCamabaStaging');
    }

    public function restore(AuthUser $authUser, PmbCamabaStaging $pmbCamabaStaging): bool
    {
        return $authUser->can('Restore:PmbCamabaStaging');
    }

    public function forceDelete(AuthUser $authUser, PmbCamabaStaging $pmbCamabaStaging): bool
    {
        return $authUser->can('ForceDelete:PmbCamabaStaging');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PmbCamabaStaging');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PmbCamabaStaging');
    }

    public function replicate(AuthUser $authUser, PmbCamabaStaging $pmbCamabaStaging): bool
    {
        return $authUser->can('Replicate:PmbCamabaStaging');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PmbCamabaStaging');
    }

}