<?php

namespace App\Filament\Resources\LpmAkreditasiKriterias\RelationManagers;

use App\Filament\Resources\LpmAkreditasiElemens\LpmAkreditasiElemenResource;
use Filament\Actions\Action;
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
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ElemensRelationManager extends RelationManager
{
    protected static string $relationship = 'elemens';

    protected static ?string $title = 'Elemen';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode_elemen')
                    ->label('Kode Elemen')
                    ->required()
                    ->maxLength(20),
                Textarea::make('deskripsi')
                    ->label('Deskripsi Butir')
                    ->required()
                    ->rows(2)
                    ->columnSpanFull(),
                TextInput::make('urutan')
                    ->label('Urutan')
                    ->numeric()
                    ->default(1)
                    ->required(),
                Select::make('status_kelengkapan')
                    ->label('Status Kelengkapan')
                    ->options([
                        'BELUM' => 'Belum',
                        'PROSES' => 'Proses',
                        'LENGKAP' => 'Lengkap',
                    ])
                    ->default('BELUM')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('deskripsi')
            ->columns([
                TextColumn::make('kode_elemen')->label('Kode'),
                TextColumn::make('deskripsi')->label('Deskripsi')->wrap()->limit(70),
                TextColumn::make('status_kelengkapan')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'LENGKAP' => 'success',
                        'PROSES' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('indikators_count')->label('Jumlah Indikator')->counts('indikators'),
                TextColumn::make('evidences_count')->label('Jumlah Evidence')->counts('evidences'),
            ])
            ->defaultSort('urutan')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                Action::make('kelolaDetail')
                    ->label('Kelola Detail')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn($record) => LpmAkreditasiElemenResource::getUrl('edit', ['record' => $record])),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->reorderable('urutan')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
