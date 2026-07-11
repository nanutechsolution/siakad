<?php

namespace App\Filament\Resources\PersonRoles;

use App\Enums\NavigationGroup;
use App\Filament\Resources\PersonRoles\Pages\CreatePersonRole;
use App\Filament\Resources\PersonRoles\Pages\EditPersonRole;
use App\Filament\Resources\PersonRoles\Pages\ListPersonRoles;
use App\Filament\Resources\PersonRoles\Schemas\PersonRoleForm;
use App\Filament\Resources\PersonRoles\Tables\PersonRolesTable;
use App\Models\RefPersonRole;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PersonRoleResource extends Resource
{
    protected static ?string $model = RefPersonRole::class;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::KEPEGAWAIAN->value;

    protected static ?string $modelLabel = 'Master Role Institusi';

    protected static ?string $pluralModelLabel = 'Master Role Institusi';

    protected static ?int $navigationSort = 3;
    public static function form(Schema $schema): Schema
    {
        return PersonRoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PersonRolesTable::configure($table);
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
            'index' => ListPersonRoles::route('/'),
        ];
    }
}
