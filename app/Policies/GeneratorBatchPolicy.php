<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\GeneratorBatch;
use Illuminate\Auth\Access\HandlesAuthorization;

class GeneratorBatchPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:GeneratorBatch');
    }

    public function view(AuthUser $authUser, GeneratorBatch $generatorBatch): bool
    {
        return $authUser->can('View:GeneratorBatch');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:GeneratorBatch');
    }

    public function update(AuthUser $authUser, GeneratorBatch $generatorBatch): bool
    {
        return $authUser->can('Update:GeneratorBatch');
    }

    public function delete(AuthUser $authUser, GeneratorBatch $generatorBatch): bool
    {
        return $authUser->can('Delete:GeneratorBatch');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:GeneratorBatch');
    }

    public function restore(AuthUser $authUser, GeneratorBatch $generatorBatch): bool
    {
        return $authUser->can('Restore:GeneratorBatch');
    }

    public function forceDelete(AuthUser $authUser, GeneratorBatch $generatorBatch): bool
    {
        return $authUser->can('ForceDelete:GeneratorBatch');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:GeneratorBatch');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:GeneratorBatch');
    }

    public function replicate(AuthUser $authUser, GeneratorBatch $generatorBatch): bool
    {
        return $authUser->can('Replicate:GeneratorBatch');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:GeneratorBatch');
    }

}