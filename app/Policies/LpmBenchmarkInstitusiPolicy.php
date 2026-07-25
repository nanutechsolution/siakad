<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LpmBenchmarkInstitusi;
use Illuminate\Auth\Access\HandlesAuthorization;

class LpmBenchmarkInstitusiPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LpmBenchmarkInstitusi');
    }

    public function view(AuthUser $authUser, LpmBenchmarkInstitusi $lpmBenchmarkInstitusi): bool
    {
        return $authUser->can('View:LpmBenchmarkInstitusi');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LpmBenchmarkInstitusi');
    }

    public function update(AuthUser $authUser, LpmBenchmarkInstitusi $lpmBenchmarkInstitusi): bool
    {
        return $authUser->can('Update:LpmBenchmarkInstitusi');
    }

    public function delete(AuthUser $authUser, LpmBenchmarkInstitusi $lpmBenchmarkInstitusi): bool
    {
        return $authUser->can('Delete:LpmBenchmarkInstitusi');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LpmBenchmarkInstitusi');
    }

    public function restore(AuthUser $authUser, LpmBenchmarkInstitusi $lpmBenchmarkInstitusi): bool
    {
        return $authUser->can('Restore:LpmBenchmarkInstitusi');
    }

    public function forceDelete(AuthUser $authUser, LpmBenchmarkInstitusi $lpmBenchmarkInstitusi): bool
    {
        return $authUser->can('ForceDelete:LpmBenchmarkInstitusi');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LpmBenchmarkInstitusi');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LpmBenchmarkInstitusi');
    }

    public function replicate(AuthUser $authUser, LpmBenchmarkInstitusi $lpmBenchmarkInstitusi): bool
    {
        return $authUser->can('Replicate:LpmBenchmarkInstitusi');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LpmBenchmarkInstitusi');
    }

}