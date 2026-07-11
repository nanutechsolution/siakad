<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PaymentPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicyPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PaymentPolicy');
    }

    public function view(AuthUser $authUser, PaymentPolicy $paymentPolicy): bool
    {
        return $authUser->can('View:PaymentPolicy');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PaymentPolicy');
    }

    public function update(AuthUser $authUser, PaymentPolicy $paymentPolicy): bool
    {
        return $authUser->can('Update:PaymentPolicy');
    }

    public function delete(AuthUser $authUser, PaymentPolicy $paymentPolicy): bool
    {
        return $authUser->can('Delete:PaymentPolicy');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PaymentPolicy');
    }

    public function restore(AuthUser $authUser, PaymentPolicy $paymentPolicy): bool
    {
        return $authUser->can('Restore:PaymentPolicy');
    }

    public function forceDelete(AuthUser $authUser, PaymentPolicy $paymentPolicy): bool
    {
        return $authUser->can('ForceDelete:PaymentPolicy');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PaymentPolicy');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PaymentPolicy');
    }

    public function replicate(AuthUser $authUser, PaymentPolicy $paymentPolicy): bool
    {
        return $authUser->can('Replicate:PaymentPolicy');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PaymentPolicy');
    }

}