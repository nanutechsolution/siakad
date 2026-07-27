<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PdfNumberSequence;
use Illuminate\Auth\Access\HandlesAuthorization;

class PdfNumberSequencePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PdfNumberSequence');
    }

    public function view(AuthUser $authUser, PdfNumberSequence $pdfNumberSequence): bool
    {
        return $authUser->can('View:PdfNumberSequence');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PdfNumberSequence');
    }

    public function update(AuthUser $authUser, PdfNumberSequence $pdfNumberSequence): bool
    {
        return $authUser->can('Update:PdfNumberSequence');
    }

    public function delete(AuthUser $authUser, PdfNumberSequence $pdfNumberSequence): bool
    {
        return $authUser->can('Delete:PdfNumberSequence');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PdfNumberSequence');
    }

    public function restore(AuthUser $authUser, PdfNumberSequence $pdfNumberSequence): bool
    {
        return $authUser->can('Restore:PdfNumberSequence');
    }

    public function forceDelete(AuthUser $authUser, PdfNumberSequence $pdfNumberSequence): bool
    {
        return $authUser->can('ForceDelete:PdfNumberSequence');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PdfNumberSequence');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PdfNumberSequence');
    }

    public function replicate(AuthUser $authUser, PdfNumberSequence $pdfNumberSequence): bool
    {
        return $authUser->can('Replicate:PdfNumberSequence');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PdfNumberSequence');
    }

}