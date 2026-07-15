<?php

namespace App\Filament\Resources\ProfileChangeRequests;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ProfileChangeRequests\Pages\CreateProfileChangeRequest;
use App\Filament\Resources\ProfileChangeRequests\Pages\EditProfileChangeRequest;
use App\Filament\Resources\ProfileChangeRequests\Pages\ListProfileChangeRequests;
use App\Filament\Resources\ProfileChangeRequests\Schemas\ProfileChangeRequestForm;
use App\Filament\Resources\ProfileChangeRequests\Tables\ProfileChangeRequestsTable;
use App\Models\ProfileChangeRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;
use UnitEnum;

class ProfileChangeRequestResource extends Resource
{
    protected static ?string $model = ProfileChangeRequest::class;

    protected static ?string $navigationLabel = 'Verifikasi Perubahan Data';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::MAHASISWA->value;
    protected static ?string $modelLabel = 'Pengajuan Perubahan Data';
    protected static ?string $pluralModelLabel = 'Pengajuan Perubahan Data';

    #[Override]
    public static function canEdit(Model $record): bool
    {
        return false;
    }

    #[Override]
    public static function canCreate(): bool
    {
        return false;
    }

    #[Override]
    public static function canView(Model $record): bool
    {
        return false;
    }
    public static function form(Schema $schema): Schema
    {
        return ProfileChangeRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProfileChangeRequestsTable::configure($table);
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
            'index' => ListProfileChangeRequests::route('/'),
        ];
    }
}
