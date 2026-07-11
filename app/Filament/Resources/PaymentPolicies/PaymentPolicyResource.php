<?php

namespace App\Filament\Resources\PaymentPolicies;

use App\Enums\NavigationGroup;
use App\Filament\Resources\PaymentPolicies\Pages\CreatePaymentPolicy;
use App\Filament\Resources\PaymentPolicies\Pages\EditPaymentPolicy;
use App\Filament\Resources\PaymentPolicies\Pages\ListPaymentPolicies;
use App\Filament\Resources\PaymentPolicies\Schemas\PaymentPolicyForm;
use App\Filament\Resources\PaymentPolicies\Tables\PaymentPoliciesTable;
use App\Models\PaymentPolicy;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PaymentPolicyResource extends Resource
{
    protected static ?string $model = PaymentPolicy::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::KEUANGAN->value;
    protected static ?string $navigationLabel = 'Kebijakan Pembayaran';
    protected static ?string $modelLabel = 'Kebijakan Pembayaran';
    protected static ?string $pluralModelLabel = 'Kebijakan Pembayaran';
    public static function form(Schema $schema): Schema
    {
        return PaymentPolicyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentPoliciesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentPolicies::route('/'),
            'create' => CreatePaymentPolicy::route('/create'),
            'edit' => EditPaymentPolicy::route('/{record}/edit'),
        ];
    }
}
