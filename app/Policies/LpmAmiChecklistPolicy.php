<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LpmAmiChecklist;
use Illuminate\Auth\Access\HandlesAuthorization;

class LpmAmiChecklistPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LpmAmiChecklist');
    }

    public function view(AuthUser $authUser, LpmAmiChecklist $lpmAmiChecklist): bool
    {
        return $authUser->can('View:LpmAmiChecklist');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LpmAmiChecklist');
    }

    public function update(AuthUser $authUser, LpmAmiChecklist $lpmAmiChecklist): bool
    {
        return $authUser->can('Update:LpmAmiChecklist');
    }

    public function delete(AuthUser $authUser, LpmAmiChecklist $lpmAmiChecklist): bool
    {
        return $authUser->can('Delete:LpmAmiChecklist');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LpmAmiChecklist');
    }

    public function restore(AuthUser $authUser, LpmAmiChecklist $lpmAmiChecklist): bool
    {
        return $authUser->can('Restore:LpmAmiChecklist');
    }

    public function forceDelete(AuthUser $authUser, LpmAmiChecklist $lpmAmiChecklist): bool
    {
        return $authUser->can('ForceDelete:LpmAmiChecklist');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LpmAmiChecklist');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LpmAmiChecklist');
    }

    public function replicate(AuthUser $authUser, LpmAmiChecklist $lpmAmiChecklist): bool
    {
        return $authUser->can('Replicate:LpmAmiChecklist');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LpmAmiChecklist');
    }

}