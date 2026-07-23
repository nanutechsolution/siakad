<?php

namespace App\Filament\Resources\LpmKuisionerKelompoks\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PertanyaansRelationManager extends RelationManager
{
    protected static string $relationship = 'pertanyaans';
    protected static ?string $title = 'Daftar Pertanyaan';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('bunyi_pertanyaan')
                    ->label('Bunyi Pertanyaan')
                    ->required()
                    ->rows(2)
                    ->columnSpanFull(),
                Select::make('jenis_input')
                    ->label('Jenis Input')
                    ->options([
                        'RATING_4' => 'Rating Skala 4',
                        'RATING_5' => 'Rating Skala 5',
                        'ESSAY' => 'Esai / Teks',
                        'BOOLEAN' => 'Ya / Tidak',
                    ])
                    ->default('RATING_4')
                    ->required(),
                TextInput::make('urutan')
                    ->label('Urutan')
                    ->numeric()
                    ->default(1)
                    ->required(),
                Toggle::make('is_required')
                    ->label('Wajib Diisi')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('bunyi_pertanyaan')
            ->columns([
                TextColumn::make('urutan')->label('#')->sortable(),
                TextColumn::make('bunyi_pertanyaan')->label('Pertanyaans')->wrap()->limit(80),
                TextColumn::make('jenis_input')->label('Jenis Input')->badge(),
                IconColumn::make('is_required')->label('Wajib')->boolean(),
            ])
            ->defaultSort('urutan')
            ->filters([
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
