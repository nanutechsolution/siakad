<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\JadwalGeneratorBatch;
use Illuminate\Auth\Access\HandlesAuthorization;

class JadwalGeneratorBatchPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:JadwalGeneratorBatch');
    }

    public function view(AuthUser $authUser, JadwalGeneratorBatch $jadwalGeneratorBatch): bool
    {
        return $authUser->can('View:JadwalGeneratorBatch');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:JadwalGeneratorBatch');
    }

    public function update(AuthUser $authUser, JadwalGeneratorBatch $jadwalGeneratorBatch): bool
    {
        return $authUser->can('Update:JadwalGeneratorBatch');
    }

    public function delete(AuthUser $authUser, JadwalGeneratorBatch $jadwalGeneratorBatch): bool
    {
        return $authUser->can('Delete:JadwalGeneratorBatch');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:JadwalGeneratorBatch');
    }

    public function restore(AuthUser $authUser, JadwalGeneratorBatch $jadwalGeneratorBatch): bool
    {
        return $authUser->can('Restore:JadwalGeneratorBatch');
    }

    public function forceDelete(AuthUser $authUser, JadwalGeneratorBatch $jadwalGeneratorBatch): bool
    {
        return $authUser->can('ForceDelete:JadwalGeneratorBatch');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:JadwalGeneratorBatch');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:JadwalGeneratorBatch');
    }

    public function replicate(AuthUser $authUser, JadwalGeneratorBatch $jadwalGeneratorBatch): bool
    {
        return $authUser->can('Replicate:JadwalGeneratorBatch');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:JadwalGeneratorBatch');
    }

}