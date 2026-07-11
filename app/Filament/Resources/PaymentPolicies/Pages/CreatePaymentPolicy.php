<?php

namespace App\Filament\Resources\PaymentPolicies\Pages;

use App\Filament\Resources\PaymentPolicies\PaymentPolicyResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentPolicy extends CreateRecord
{
    protected static string $resource = PaymentPolicyResource::class;
}
