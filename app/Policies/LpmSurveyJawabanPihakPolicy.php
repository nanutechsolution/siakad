<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LpmSurveyJawabanPihak;
use Illuminate\Auth\Access\HandlesAuthorization;

class LpmSurveyJawabanPihakPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LpmSurveyJawabanPihak');
    }

    public function view(AuthUser $authUser, LpmSurveyJawabanPihak $lpmSurveyJawabanPihak): bool
    {
        return $authUser->can('View:LpmSurveyJawabanPihak');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LpmSurveyJawabanPihak');
    }

    public function update(AuthUser $authUser, LpmSurveyJawabanPihak $lpmSurveyJawabanPihak): bool
    {
        return $authUser->can('Update:LpmSurveyJawabanPihak');
    }

    public function delete(AuthUser $authUser, LpmSurveyJawabanPihak $lpmSurveyJawabanPihak): bool
    {
        return $authUser->can('Delete:LpmSurveyJawabanPihak');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LpmSurveyJawabanPihak');
    }

    public function restore(AuthUser $authUser, LpmSurveyJawabanPihak $lpmSurveyJawabanPihak): bool
    {
        return $authUser->can('Restore:LpmSurveyJawabanPihak');
    }

    public function forceDelete(AuthUser $authUser, LpmSurveyJawabanPihak $lpmSurveyJawabanPihak): bool
    {
        return $authUser->can('ForceDelete:LpmSurveyJawabanPihak');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LpmSurveyJawabanPihak');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LpmSurveyJawabanPihak');
    }

    public function replicate(AuthUser $authUser, LpmSurveyJawabanPihak $lpmSurveyJawabanPihak): bool
    {
        return $authUser->can('Replicate:LpmSurveyJawabanPihak');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LpmSurveyJawabanPihak');
    }

}