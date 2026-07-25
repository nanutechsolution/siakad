<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LpmBenchmark;
use Illuminate\Auth\Access\HandlesAuthorization;

class LpmBenchmarkPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LpmBenchmark');
    }

    public function view(AuthUser $authUser, LpmBenchmark $lpmBenchmark): bool
    {
        return $authUser->can('View:LpmBenchmark');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LpmBenchmark');
    }

    public function update(AuthUser $authUser, LpmBenchmark $lpmBenchmark): bool
    {
        return $authUser->can('Update:LpmBenchmark');
    }

    public function delete(AuthUser $authUser, LpmBenchmark $lpmBenchmark): bool
    {
        return $authUser->can('Delete:LpmBenchmark');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LpmBenchmark');
    }

    public function restore(AuthUser $authUser, LpmBenchmark $lpmBenchmark): bool
    {
        return $authUser->can('Restore:LpmBenchmark');
    }

    public function forceDelete(AuthUser $authUser, LpmBenchmark $lpmBenchmark): bool
    {
        return $authUser->can('ForceDelete:LpmBenchmark');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LpmBenchmark');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LpmBenchmark');
    }

    public function replicate(AuthUser $authUser, LpmBenchmark $lpmBenchmark): bool
    {
        return $authUser->can('Replicate:LpmBenchmark');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LpmBenchmark');
    }

}