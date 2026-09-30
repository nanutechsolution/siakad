<?php

declare(strict_types=1);

namespace App\Filament\Resources\OidcClients;

use App\Enums\NavigationGroup;
use App\Filament\Resources\OidcClients\Pages\CreateOidcClient;
use App\Filament\Resources\OidcClients\Pages\EditOidcClient;
use App\Filament\Resources\OidcClients\Pages\ListOidcClients;
use App\Filament\Resources\OidcClients\Schemas\OidcClientForm;
use App\Filament\Resources\OidcClients\Tables\OidcClientsTable;
use App\Models\OidcClient;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class OidcClientResource extends Resource
{
    protected static ?string $model = OidcClient::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::INTEGRASI->value;

    protected static ?string $modelLabel = 'Client SSO (OIDC)';

    protected static ?string $pluralModelLabel = 'Client SSO (OIDC)';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return OidcClientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OidcClientsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOidcClients::route('/'),
            'create' => CreateOidcClient::route('/create'),
            'edit' => EditOidcClient::route('/{record}/edit'),
        ];
    }
}
