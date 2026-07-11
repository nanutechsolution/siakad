<?php

namespace App\Filament\Resources\PaymentPolicies\Pages;

use App\Filament\Resources\PaymentPolicies\PaymentPolicyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPaymentPolicies extends ListRecords
{
    protected static string $resource = PaymentPolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
