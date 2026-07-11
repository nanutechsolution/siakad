<?php

namespace App\Filament\Resources\PaymentPolicies\Pages;

use App\Filament\Resources\PaymentPolicies\PaymentPolicyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPaymentPolicy extends EditRecord
{
    protected static string $resource = PaymentPolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
