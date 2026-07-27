<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PdfSignatureAuthority;
use Illuminate\Auth\Access\HandlesAuthorization;

class PdfSignatureAuthorityPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PdfSignatureAuthority');
    }

    public function view(AuthUser $authUser, PdfSignatureAuthority $pdfSignatureAuthority): bool
    {
        return $authUser->can('View:PdfSignatureAuthority');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PdfSignatureAuthority');
    }

    public function update(AuthUser $authUser, PdfSignatureAuthority $pdfSignatureAuthority): bool
    {
        return $authUser->can('Update:PdfSignatureAuthority');
    }

    public function delete(AuthUser $authUser, PdfSignatureAuthority $pdfSignatureAuthority): bool
    {
        return $authUser->can('Delete:PdfSignatureAuthority');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PdfSignatureAuthority');
    }

    public function restore(AuthUser $authUser, PdfSignatureAuthority $pdfSignatureAuthority): bool
    {
        return $authUser->can('Restore:PdfSignatureAuthority');
    }

    public function forceDelete(AuthUser $authUser, PdfSignatureAuthority $pdfSignatureAuthority): bool
    {
        return $authUser->can('ForceDelete:PdfSignatureAuthority');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PdfSignatureAuthority');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PdfSignatureAuthority');
    }

    public function replicate(AuthUser $authUser, PdfSignatureAuthority $pdfSignatureAuthority): bool
    {
        return $authUser->can('Replicate:PdfSignatureAuthority');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PdfSignatureAuthority');
    }

}