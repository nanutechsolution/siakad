<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Authorization\Services\DataVisibilityResolver;
use App\Models\PembimbingAkademik;
use App\Models\User;
use App\Policies\Concerns\AuthorizesViaScope;
use Illuminate\Auth\Access\HandlesAuthorization;

final class PembimbingAkademikPolicy
{
    use AuthorizesViaScope;
    use HandlesAuthorization;

    public function __construct(DataVisibilityResolver $visibility)
    {
        $this->visibility = $visibility;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:PembimbingAkademik')
            || $user->hasAnyRole(['super_admin', 'BAAK', 'Admin Akademik', 'Admin Fakultas', 'Admin Prodi', 'Kaprodi', 'Dosen Wali', 'Dosen']);
    }

    public function view(User $user, PembimbingAkademik $assignment): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        // Dosen penanggung jawab penugasan selalu boleh melihat SK-nya sendiri,
        // termasuk untuk role Dosen biasa yang tidak tercakup scope organisasi.
        $dosen = $user->person?->dosen;

        if ($dosen !== null && $assignment->dosen_id === $dosen->id) {
            return true;
        }

        return $this->isRecordAccessible($user, $assignment);
    }
}
