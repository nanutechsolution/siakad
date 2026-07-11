<?php

namespace App\Filament\Resources\RefPrograms;

use App\Enums\NavigationGroup;
use App\Filament\Resources\RefPrograms\Pages\CreateRefProgram;
use App\Filament\Resources\RefPrograms\Pages\EditRefProgram;
use App\Filament\Resources\RefPrograms\Pages\ListRefPrograms;
use App\Filament\Resources\RefPrograms\Schemas\RefProgramForm;
use App\Filament\Resources\RefPrograms\Tables\RefProgramsTable;
use App\Models\RefProgram;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class RefProgramResource extends Resource
{
    protected static ?string $model = RefProgram::class;



    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::MASTER->value;

    protected static ?string $modelLabel = 'Program';

    protected static ?string $pluralModelLabel = 'Program';
    public static function form(Schema $schema): Schema
    {
        return RefProgramForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RefProgramsTable::configure($table);
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
            'index' => ListRefPrograms::route('/'),
            'create' => CreateRefProgram::route('/create'),
            'edit' => EditRefProgram::route('/{record}/edit'),
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
