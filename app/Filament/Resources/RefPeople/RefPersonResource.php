<?php

namespace App\Filament\Resources\RefPeople;

use App\Enums\NavigationGroup;
use App\Filament\Resources\RefPeople\Pages\CreateRefPerson;
use App\Filament\Resources\RefPeople\Pages\EditRefPerson;
use App\Filament\Resources\RefPeople\Pages\ListRefPeople;
use App\Filament\Resources\RefPeople\Pages\ViewRefPerson;
use App\Filament\Resources\RefPeople\Schemas\RefPersonForm;
use App\Filament\Resources\RefPeople\Schemas\RefPersonInfolist;
use App\Filament\Resources\RefPeople\Tables\RefPeopleTable;
use App\Models\RefPerson;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class RefPersonResource extends Resource
{
    protected static ?string $model = RefPerson::class;
    protected static ?string $modelLabel = 'Data Person (SSOT)';

    protected static ?string $pluralModelLabel = 'Data Person';

    protected static ?int $navigationSort = 99;
    protected static ?string $recordTitleAttribute = 'nama_lengkap';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::KEPEGAWAIAN->value;

    public static function getGloballySearchableAttributes(): array
    {
        return ['nama_lengkap', 'nik', 'email', 'no_hp'];
    }
    public static function form(Schema $schema): Schema
    {
        return RefPersonForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RefPersonInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RefPeopleTable::configure($table);
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
            'index' => ListRefPeople::route('/'),
            'create' => CreateRefPerson::route('/create'),
            'view' => ViewRefPerson::route('/{record}'),
            'edit' => EditRefPerson::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
