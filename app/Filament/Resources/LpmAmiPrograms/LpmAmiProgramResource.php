<?php

namespace App\Filament\Resources\LpmAmiPrograms;

use App\Enums\NavigationGroup;
use App\Filament\Resources\LpmAmiPrograms\Pages\CreateLpmAmiProgram;
use App\Filament\Resources\LpmAmiPrograms\Pages\EditLpmAmiProgram;
use App\Filament\Resources\LpmAmiPrograms\Pages\ListLpmAmiPrograms;
use App\Filament\Resources\LpmAmiPrograms\RelationManagers\ChecklistJawabansRelationManager;
use App\Filament\Resources\LpmAmiPrograms\RelationManagers\ProgramAuditorsRelationManager;
use App\Filament\Resources\LpmAmiPrograms\Schemas\LpmAmiProgramForm;
use App\Filament\Resources\LpmAmiPrograms\Tables\LpmAmiProgramsTable;
use App\Models\LpmAmiProgram;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LpmAmiProgramResource extends Resource
{
    protected static ?string $model = LpmAmiProgram::class;


    protected static ?string $navigationLabel = 'Program Audit';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::LPM->value;

    protected static ?string $modelLabel = 'Program Audit';

    public static function form(Schema $schema): Schema
    {
        return LpmAmiProgramForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LpmAmiProgramsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ProgramAuditorsRelationManager::class,
            ChecklistJawabansRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLpmAmiPrograms::route('/'),
            'create' => CreateLpmAmiProgram::route('/create'),
            'edit' => EditLpmAmiProgram::route('/{record}/edit'),
        ];
    }
}
