<?php

namespace App\Filament\Resources\TrxDosens;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Pegawais\RelationManagers\AtribusiGelarRelationManager;
use App\Filament\Resources\Pegawais\RelationManagers\RiwayatJabatanRelationManager;
use App\Filament\Resources\Pegawais\RelationManagers\RiwayatRoleRelationManager;
use App\Filament\Resources\TrxDosens\Pages\CreateTrxDosen;
use App\Filament\Resources\TrxDosens\Pages\EditTrxDosen;
use App\Filament\Resources\TrxDosens\Pages\ListTrxDosens;
use App\Filament\Resources\TrxDosens\Schemas\TrxDosenForm;
use App\Filament\Resources\TrxDosens\Tables\TrxDosensTable;
use App\Models\TrxDosen;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class TrxDosenResource extends Resource
{
    protected static ?string $model = TrxDosen::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::KEPEGAWAIAN->value;
    protected static ?string $modelLabel = 'Dosen';
    protected static ?string $pluralModelLabel = 'Dosen';
    protected static ?string $recordTitleAttribute = 'person.nama';
    public static function getEloquentQuery(): Builder
    {
        return  parent::getEloquentQuery()->visibleTo(auth()->user());
        // return parent::getEloquentQuery()
        // ->with([
        //     'person.gelars',
        // ]);
    }
    public static function form(Schema $schema): Schema
    {
        return TrxDosenForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrxDosensTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AtribusiGelarRelationManager::class,
            RiwayatJabatanRelationManager::class,
            RiwayatRoleRelationManager::class,

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrxDosens::route('/'),
            'create' => CreateTrxDosen::route('/create'),
            'edit' => EditTrxDosen::route('/{record}/edit'),
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
