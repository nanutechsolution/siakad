<?php

namespace App\Filament\Resources\DosenProfileChangeRequests;

use App\Enums\NavigationGroup;
use App\Filament\Resources\DosenProfileChangeRequests\Pages\CreateDosenProfileChangeRequest;
use App\Filament\Resources\DosenProfileChangeRequests\Pages\EditDosenProfileChangeRequest;
use App\Filament\Resources\DosenProfileChangeRequests\Pages\ListDosenProfileChangeRequests;
use App\Filament\Resources\DosenProfileChangeRequests\Schemas\DosenProfileChangeRequestForm;
use App\Filament\Resources\DosenProfileChangeRequests\Tables\DosenProfileChangeRequestsTable;
use App\Models\DosenProfileChangeRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DosenProfileChangeRequestResource extends Resource
{
    protected static ?string $model = DosenProfileChangeRequest::class;

    protected static ?string $navigationLabel = 'Verifikasi Data Dosen';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::KEPEGAWAIAN->value;
    protected static ?string $modelLabel = 'Pengajuan Perubahan Data Dosen';
    protected static ?string $pluralModelLabel = 'Pengajuan Perubahan Data Dosen';

    public static function form(Schema $schema): Schema
    {
        return DosenProfileChangeRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DosenProfileChangeRequestsTable::configure($table);
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
            'index' => ListDosenProfileChangeRequests::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
