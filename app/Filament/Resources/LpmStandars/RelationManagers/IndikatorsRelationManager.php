<?php

namespace App\Filament\Resources\LpmStandars\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class IndikatorsRelationManager extends RelationManager
{
    protected static string $relationship = 'indikators';
    protected static ?string $title = 'Indikator';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode_indikator')
                    ->label('Kode Indikator')
                    ->maxLength(20),
                TextInput::make('nama_indikator')
                    ->label('Nama Indikator')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('satuan')
                    ->label('Satuan')
                    ->maxLength(50)
                    ->helperText('Contoh: %, Orang, Dokumen'),
                TextInput::make('bobot')
                    ->label('Bobot')
                    ->numeric()
                    ->default(1)
                    ->required(),
                TextInput::make('sumber_data_siakad')
                    ->label('Sumber Data SIAKAD (opsional)')
                    ->maxLength(255)
                    ->helperText('Nama tabel/kolom sumber data otomatis, kalau ada.'),
                Toggle::make('is_iku')
                    ->label('Termasuk IKU')
                    ->default(true),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_indikator')
            ->columns([
                TextColumn::make('kode_indikator')->label('Kode'),
                TextColumn::make('nama_indikator')->label('Nama Indikator')->wrap(),
                TextColumn::make('satuan')->label('Satuan'),
                TextColumn::make('bobot')->label('Bobot')->numeric(2),
                IconColumn::make('is_iku')->label('IKU')->boolean(),
                TextColumn::make('ikuTargets_count')->label('Jumlah Target')->counts('ikuTargets'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
