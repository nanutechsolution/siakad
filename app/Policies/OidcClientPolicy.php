<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\OidcClient;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class OidcClientPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OidcClient');
    }

    public function view(AuthUser $authUser, OidcClient $oidcClient): bool
    {
        return $authUser->can('View:OidcClient');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OidcClient');
    }

    public function update(AuthUser $authUser, OidcClient $oidcClient): bool
    {
        return $authUser->can('Update:OidcClient');
    }

    public function delete(AuthUser $authUser, OidcClient $oidcClient): bool
    {
        return $authUser->can('Delete:OidcClient');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:OidcClient');
    }
}
