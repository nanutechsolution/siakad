<?php

namespace App\Filament\Resources\KeuanganAdjustments\Pages;

use App\Filament\Resources\KeuanganAdjustments\KeuanganAdjustmentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateKeuanganAdjustment extends CreateRecord
{
    protected static string $resource = KeuanganAdjustmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        return $data;
    }
}
