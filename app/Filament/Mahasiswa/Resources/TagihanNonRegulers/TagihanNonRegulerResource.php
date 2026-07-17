<?php

namespace App\Filament\Mahasiswa\Resources\TagihanNonRegulers;

use App\Enums\MahasiswaNavigationGroup;
use App\Filament\Mahasiswa\Resources\TagihanNonRegulers\Pages\CreateTagihanNonReguler;
use App\Filament\Mahasiswa\Resources\TagihanNonRegulers\Pages\EditTagihanNonReguler;
use App\Filament\Mahasiswa\Resources\TagihanNonRegulers\Pages\ListTagihanNonRegulers;
use App\Filament\Mahasiswa\Resources\TagihanNonRegulers\Pages\ViewTagihanNonReguler;
use App\Filament\Mahasiswa\Resources\TagihanNonRegulers\Schemas\TagihanNonRegulerForm;
use App\Filament\Mahasiswa\Resources\TagihanNonRegulers\Schemas\TagihanNonRegulerInfolist;
use App\Filament\Mahasiswa\Resources\TagihanNonRegulers\Tables\TagihanNonRegulersTable;
use App\Models\Mahasiswa;
use App\Models\TagihanNonReguler;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Override;
use UnitEnum;

class TagihanNonRegulerResource extends Resource
{
    protected static ?string $model = TagihanNonReguler::class;
    protected static string|UnitEnum|null $navigationGroup = MahasiswaNavigationGroup::KEUANGAN->value;
    protected static ?string $navigationLabel = 'Tagihan Non Reguler';
    protected static ?string $modelLabel = 'Tagihan Non Reguler';
    protected static ?string $pluralModelLabel = 'Daftar Tagihan Non Reguler';
    /**
     * Memotong query dari hulu agar mahasiswa HANYA bisa melihat tagihan miliknya sendiri.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->milikMahasiswa(auth()->user()->mahasiswa->id ?? '__none__');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
    public static function canViewAny(): bool
    {
        return true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
    public static function form(Schema $schema): Schema
    {
        return TagihanNonRegulerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TagihanNonRegulerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TagihanNonRegulersTable::configure($table);
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
            'index' => ListTagihanNonRegulers::route('/'),
            'view' => ViewTagihanNonReguler::route('/{record}'),
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
