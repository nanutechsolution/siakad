<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PdfDocument;
use Illuminate\Auth\Access\HandlesAuthorization;

class PdfDocumentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PdfDocument');
    }

    public function view(AuthUser $authUser, PdfDocument $pdfDocument): bool
    {
        return $authUser->can('View:PdfDocument');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PdfDocument');
    }

    public function update(AuthUser $authUser, PdfDocument $pdfDocument): bool
    {
        return $authUser->can('Update:PdfDocument');
    }

    public function delete(AuthUser $authUser, PdfDocument $pdfDocument): bool
    {
        return $authUser->can('Delete:PdfDocument');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PdfDocument');
    }

    public function restore(AuthUser $authUser, PdfDocument $pdfDocument): bool
    {
        return $authUser->can('Restore:PdfDocument');
    }

    public function forceDelete(AuthUser $authUser, PdfDocument $pdfDocument): bool
    {
        return $authUser->can('ForceDelete:PdfDocument');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PdfDocument');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PdfDocument');
    }

    public function replicate(AuthUser $authUser, PdfDocument $pdfDocument): bool
    {
        return $authUser->can('Replicate:PdfDocument');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PdfDocument');
    }

}