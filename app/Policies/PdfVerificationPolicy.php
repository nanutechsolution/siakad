<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PdfVerification;
use Illuminate\Auth\Access\HandlesAuthorization;

class PdfVerificationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PdfVerification');
    }

    public function view(AuthUser $authUser, PdfVerification $pdfVerification): bool
    {
        return $authUser->can('View:PdfVerification');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PdfVerification');
    }

    public function update(AuthUser $authUser, PdfVerification $pdfVerification): bool
    {
        return $authUser->can('Update:PdfVerification');
    }

    public function delete(AuthUser $authUser, PdfVerification $pdfVerification): bool
    {
        return $authUser->can('Delete:PdfVerification');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PdfVerification');
    }

    public function restore(AuthUser $authUser, PdfVerification $pdfVerification): bool
    {
        return $authUser->can('Restore:PdfVerification');
    }

    public function forceDelete(AuthUser $authUser, PdfVerification $pdfVerification): bool
    {
        return $authUser->can('ForceDelete:PdfVerification');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PdfVerification');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PdfVerification');
    }

    public function replicate(AuthUser $authUser, PdfVerification $pdfVerification): bool
    {
        return $authUser->can('Replicate:PdfVerification');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PdfVerification');
    }

}