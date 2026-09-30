<?php

declare(strict_types=1);

namespace App\Filament\Resources\OidcClients\Pages;

use App\Filament\Resources\OidcClients\OidcClientResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOidcClients extends ListRecords
{
    protected static string $resource = OidcClientResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
