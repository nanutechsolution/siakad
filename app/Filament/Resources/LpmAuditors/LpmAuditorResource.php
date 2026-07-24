<?php

namespace App\Filament\Resources\LpmAuditors;

use App\Enums\NavigationGroup;
use App\Filament\Resources\LpmAuditors\Pages\CreateLpmAuditor;
use App\Filament\Resources\LpmAuditors\Pages\EditLpmAuditor;
use App\Filament\Resources\LpmAuditors\Pages\ListLpmAuditors;
use App\Filament\Resources\LpmAuditors\Schemas\LpmAuditorForm;
use App\Filament\Resources\LpmAuditors\Tables\LpmAuditorsTable;
use App\Models\LpmAuditor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LpmAuditorResource extends Resource
{
    protected static ?string $model = LpmAuditor::class;
    protected static ?string $navigationLabel = 'Auditor';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::LPM->value;
    protected static ?string $modelLabel = 'Auditor';
    public static function form(Schema $schema): Schema
    {
        return LpmAuditorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LpmAuditorsTable::configure($table);
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
            'index' => ListLpmAuditors::route('/'),
            'create' => CreateLpmAuditor::route('/create'),
            'edit' => EditLpmAuditor::route('/{record}/edit'),
        ];
    }
}
