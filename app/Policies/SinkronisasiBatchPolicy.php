<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SinkronisasiBatch;
use Illuminate\Auth\Access\HandlesAuthorization;

class SinkronisasiBatchPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SinkronisasiBatch');
    }

    public function view(AuthUser $authUser, SinkronisasiBatch $sinkronisasiBatch): bool
    {
        return $authUser->can('View:SinkronisasiBatch');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SinkronisasiBatch');
    }

    public function update(AuthUser $authUser, SinkronisasiBatch $sinkronisasiBatch): bool
    {
        return $authUser->can('Update:SinkronisasiBatch');
    }

    public function delete(AuthUser $authUser, SinkronisasiBatch $sinkronisasiBatch): bool
    {
        return $authUser->can('Delete:SinkronisasiBatch');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SinkronisasiBatch');
    }

    public function restore(AuthUser $authUser, SinkronisasiBatch $sinkronisasiBatch): bool
    {
        return $authUser->can('Restore:SinkronisasiBatch');
    }

    public function forceDelete(AuthUser $authUser, SinkronisasiBatch $sinkronisasiBatch): bool
    {
        return $authUser->can('ForceDelete:SinkronisasiBatch');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SinkronisasiBatch');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SinkronisasiBatch');
    }

    public function replicate(AuthUser $authUser, SinkronisasiBatch $sinkronisasiBatch): bool
    {
        return $authUser->can('Replicate:SinkronisasiBatch');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SinkronisasiBatch');
    }

}