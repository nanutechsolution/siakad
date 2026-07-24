<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MigrationBatch;
use Illuminate\Auth\Access\HandlesAuthorization;

class MigrationBatchPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MigrationBatch');
    }

    public function view(AuthUser $authUser, MigrationBatch $migrationBatch): bool
    {
        return $authUser->can('View:MigrationBatch');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MigrationBatch');
    }

    public function update(AuthUser $authUser, MigrationBatch $migrationBatch): bool
    {
        return $authUser->can('Update:MigrationBatch');
    }

    public function delete(AuthUser $authUser, MigrationBatch $migrationBatch): bool
    {
        return $authUser->can('Delete:MigrationBatch');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MigrationBatch');
    }

    public function restore(AuthUser $authUser, MigrationBatch $migrationBatch): bool
    {
        return $authUser->can('Restore:MigrationBatch');
    }

    public function forceDelete(AuthUser $authUser, MigrationBatch $migrationBatch): bool
    {
        return $authUser->can('ForceDelete:MigrationBatch');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MigrationBatch');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MigrationBatch');
    }

    public function replicate(AuthUser $authUser, MigrationBatch $migrationBatch): bool
    {
        return $authUser->can('Replicate:MigrationBatch');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MigrationBatch');
    }

}