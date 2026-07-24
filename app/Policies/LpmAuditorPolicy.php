<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LpmAuditor;
use Illuminate\Auth\Access\HandlesAuthorization;

class LpmAuditorPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LpmAuditor');
    }

    public function view(AuthUser $authUser, LpmAuditor $lpmAuditor): bool
    {
        return $authUser->can('View:LpmAuditor');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LpmAuditor');
    }

    public function update(AuthUser $authUser, LpmAuditor $lpmAuditor): bool
    {
        return $authUser->can('Update:LpmAuditor');
    }

    public function delete(AuthUser $authUser, LpmAuditor $lpmAuditor): bool
    {
        return $authUser->can('Delete:LpmAuditor');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LpmAuditor');
    }

    public function restore(AuthUser $authUser, LpmAuditor $lpmAuditor): bool
    {
        return $authUser->can('Restore:LpmAuditor');
    }

    public function forceDelete(AuthUser $authUser, LpmAuditor $lpmAuditor): bool
    {
        return $authUser->can('ForceDelete:LpmAuditor');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LpmAuditor');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LpmAuditor');
    }

    public function replicate(AuthUser $authUser, LpmAuditor $lpmAuditor): bool
    {
        return $authUser->can('Replicate:LpmAuditor');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LpmAuditor');
    }

}