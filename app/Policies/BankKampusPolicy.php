<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BankKampus;
use Illuminate\Auth\Access\HandlesAuthorization;

class BankKampusPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BankKampus');
    }

    public function view(AuthUser $authUser, BankKampus $bankKampus): bool
    {
        return $authUser->can('View:BankKampus');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BankKampus');
    }

    public function update(AuthUser $authUser, BankKampus $bankKampus): bool
    {
        return $authUser->can('Update:BankKampus');
    }

    public function delete(AuthUser $authUser, BankKampus $bankKampus): bool
    {
        return $authUser->can('Delete:BankKampus');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:BankKampus');
    }

    public function restore(AuthUser $authUser, BankKampus $bankKampus): bool
    {
        return $authUser->can('Restore:BankKampus');
    }

    public function forceDelete(AuthUser $authUser, BankKampus $bankKampus): bool
    {
        return $authUser->can('ForceDelete:BankKampus');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BankKampus');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BankKampus');
    }

    public function replicate(AuthUser $authUser, BankKampus $bankKampus): bool
    {
        return $authUser->can('Replicate:BankKampus');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BankKampus');
    }

}