<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

use App\Domain\Authorization\Services\DataVisibilityResolver;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Tambahkan ke setiap Policy yang modelnya implement HasScopeStrategy.
 * Menyediakan isRecordAccessible() sebagai basis view/update/delete —
 * SATU-SATUNYA tempat "apakah record ini boleh dilihat user" dihitung,
 * memakai aturan yang PERSIS SAMA dengan yang dipakai Model::visibleTo()
 * di Filament Table. Policy tidak pernah menulis ulang logic scope-nya
 * sendiri, hanya menambahkan syarat AKSI (create/approve/publish/dst)
 * di atasnya lewat PermissionResolver.
 */
trait AuthorizesViaScope
{
    /**
     * Diisi oleh constructor Policy yang memakai trait ini, mis.:
     *     public function __construct(DataVisibilityResolver $visibility, ...)
     *     {
     *         $this->visibility = $visibility;
     *     }
     */
    protected readonly DataVisibilityResolver $visibility;

    protected function isRecordAccessible(User $user, Model $record): bool
    {
        return $this->visibility->isAccessible($user, $record);
    }
}