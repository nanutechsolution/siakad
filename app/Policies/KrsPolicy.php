<?php

declare(strict_types=1);

/**
 * CUSTOM POLICY -- JANGAN generate ulang lewat `php artisan shield:generate`
 * TANPA flag --ignore-existing-policies. File ini pernah tertimpa total oleh
 * Shield sebelumnya (18 Juli 2026) -- method approve()/reject()/cancel() dan
 * seluruh scope check DataVisibilityResolver hilang, harus di-restore manual.
 *
 * Command yang aman ke depannya:
 *     php artisan shield:generate --all --ignore-existing-policies
 */

namespace App\Policies;

use App\Domain\Authorization\Services\DataVisibilityResolver;
use App\Enums\KrsStatusEnum;
use App\Models\Krs;
use App\Models\User;
use App\Policies\Concerns\AuthorizesViaScope;
use Illuminate\Auth\Access\HandlesAuthorization;

class KrsPolicy
{
    use AuthorizesViaScope;
    use HandlesAuthorization;

    public function __construct(DataVisibilityResolver $visibility)
    {
        $this->visibility = $visibility;
    }

    /*
    |--------------------------------------------------------------------------
    | Gate level modul (hasil Shield: permission "Xxx:Krs") -- tidak butuh
    | record spesifik, jadi cukup permission check.
    |--------------------------------------------------------------------------
    */

    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:Krs');
    }

    public function create(User $user): bool
    {
        return $user->can('Create:Krs');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('DeleteAny:Krs');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('RestoreAny:Krs');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('ForceDeleteAny:Krs');
    }

    public function reorder(User $user): bool
    {
        return $user->can('Reorder:Krs');
    }

    /*
    |--------------------------------------------------------------------------
    | Gate per-record -- permission Shield DAN scope organisasi (isRecordAccessible)
    | harus dua-duanya lolos. Ini yang hilang saat Shield menimpa file lama.
    |--------------------------------------------------------------------------
    */

    public function view(User $user, Krs $krs): bool
    {
        return $user->can('View:Krs') && $this->isRecordAccessible($user, $krs);
    }

    public function update(User $user, Krs $krs): bool
    {
        // Mahasiswa hanya boleh ubah KRS miliknya sendiri selagi masih DRAFT.
        if ($user->isMahasiswa()) {
            return $this->isRecordAccessible($user, $krs) && $krs->status_krs === KrsStatusEnum::DRAFT;
        }

        return $user->can('Update:Krs') && $this->isRecordAccessible($user, $krs);
    }

    public function delete(User $user, Krs $krs): bool
    {
        return $user->can('Delete:Krs') && $this->isRecordAccessible($user, $krs);
    }

    public function restore(User $user, Krs $krs): bool
    {
        return $user->can('Restore:Krs') && $this->isRecordAccessible($user, $krs);
    }

    public function forceDelete(User $user, Krs $krs): bool
    {
        return $user->can('ForceDelete:Krs') && $this->isRecordAccessible($user, $krs);
    }

    public function replicate(User $user, Krs $krs): bool
    {
        return $user->can('Replicate:Krs') && $this->isRecordAccessible($user, $krs);
    }

    /*
    |--------------------------------------------------------------------------
    | Aksi domain-spesifik (approve/reject/cancel) -- BUKAN gate standar
    | Filament, Shield TIDAK generate permission untuk ini secara otomatis.
    | Tetap pakai role check manual seperti sebelumnya.
    |--------------------------------------------------------------------------
    */

    public function approve(User $user, Krs $krs): bool
    {
        return $this->isRecordAccessible($user, $krs)
            && $krs->status_krs === KrsStatusEnum::DIAJUKAN
            && $user->hasAnyRole(['Dosen Wali', 'Admin Prodi', 'Kaprodi', 'BAAK', 'super_admin']);
    }

    public function reject(User $user, Krs $krs): bool
    {
        return $this->approve($user, $krs);
    }

    public function cancel(User $user, Krs $krs): bool
    {
        return $this->isRecordAccessible($user, $krs)
            && $user->hasAnyRole(['super_admin', 'BAAK', 'Admin Prodi', 'Kaprodi']);
    }
}