<?php

declare(strict_types=1);

/**
 * CUSTOM POLICY -- JANGAN generate ulang lewat `php artisan shield:generate`
 * TANPA flag --ignore-existing-policies.
 *
 * Command yang aman ke depannya:
 *     php artisan shield:generate --all --ignore-existing-policies
 */

namespace App\Policies;

use App\Domain\Authorization\Services\DataVisibilityResolver;
use App\Domain\Authorization\Services\PermissionResolver;
use App\Models\Mahasiswa;
use App\Models\User;
use App\Policies\Concerns\AuthorizesViaScope;
use Illuminate\Auth\Access\HandlesAuthorization;

class MahasiswaPolicy
{
    use AuthorizesViaScope;
    use HandlesAuthorization;

    public function __construct(
        DataVisibilityResolver $visibility,
        private readonly PermissionResolver $permissionResolver,
    ) {
        $this->visibility = $visibility;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:Mahasiswa')
            || $this->permissionResolver->canManageAkademik($user)
            || $user->hasAnyRole(['Dosen', 'Dosen Wali', 'Mahasiswa']);
    }

    public function create(User $user): bool
    {
        return $user->can('Create:Mahasiswa') && $this->permissionResolver->canManageAkademik($user);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('DeleteAny:Mahasiswa') && $user->hasRole('super_admin');
    }

    public function view(User $user, Mahasiswa $mahasiswa): bool
    {
        return $user->can('View:Mahasiswa') && $this->isRecordAccessible($user, $mahasiswa);
    }

    public function update(User $user, Mahasiswa $mahasiswa): bool
    {
        return $user->can('Update:Mahasiswa')
            && $this->isRecordAccessible($user, $mahasiswa)
            && $this->permissionResolver->canManageAkademik($user);
    }

    public function delete(User $user, Mahasiswa $mahasiswa): bool
    {
        return $user->can('Delete:Mahasiswa')
            && $this->isRecordAccessible($user, $mahasiswa)
            && $user->hasRole('super_admin');
    }

    public function restore(User $user, Mahasiswa $mahasiswa): bool
    {
        return $user->can('Restore:Mahasiswa') && $this->isRecordAccessible($user, $mahasiswa);
    }

    public function forceDelete(User $user, Mahasiswa $mahasiswa): bool
    {
        return $user->can('ForceDelete:Mahasiswa') && $user->hasRole('super_admin');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('ForceDeleteAny:Mahasiswa') && $user->hasRole('super_admin');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('RestoreAny:Mahasiswa');
    }

    public function replicate(User $user, Mahasiswa $mahasiswa): bool
    {
        return $user->can('Replicate:Mahasiswa') && $this->isRecordAccessible($user, $mahasiswa);
    }

    public function reorder(User $user): bool
    {
        return $user->can('Reorder:Mahasiswa');
    }
}