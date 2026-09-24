<?php

namespace App\Filament\Resources\Krs;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Krs\Pages\CreateKrs;
use App\Filament\Resources\Krs\Pages\EditKrs;
use App\Filament\Resources\Krs\Pages\ListKrs;
use App\Filament\Resources\Krs\Pages\ViewKrs;
use App\Filament\Resources\Krs\Schemas\KrsForm;
use App\Filament\Resources\Krs\Schemas\KrsInfolist;
use App\Filament\Resources\Krs\Tables\KrsTable;
use App\Models\Krs;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KrsResource extends Resource
{
    protected static ?string $model = Krs::class;

    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::AKADEMIK->value;
    protected static ?string $navigationLabel = 'KRS Mahasiswa';
    protected static ?string $modelLabel = 'Kartu Rencana Studi';
    protected static ?string $pluralModelLabel = 'Kartu Rencana Studi';
    public static function form(Schema $schema): Schema
    {
        return KrsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return KrsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KrsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'mahasiswa.person',
                'mahasiswa.prodi',
                'mahasiswa.tagihanMahasiswas',
                'tahunAkademik',
                'details.mataKuliah',
                'details.jadwalKuliah.ruang',
            ])
            ->visibleTo(auth()->user());
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKrs::route('/'),
            // Create hanya terbuka bagi pemegang Create:Krs (BAAK/super_admin)
            // — Admin Prodi tidak diberi permission itu, jadi hanya review.
            'create' => CreateKrs::route('/create'),
            'view' => ViewKrs::route('/{record}'),
            'edit' => EditKrs::route('/{record}/edit'),
        ];
    }
}
