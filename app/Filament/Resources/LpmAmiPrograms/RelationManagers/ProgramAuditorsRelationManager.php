<?php

namespace App\Filament\Resources\LpmAmiPrograms\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProgramAuditorsRelationManager extends RelationManager
{
    protected static string $relationship = 'programAuditors';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('auditor_id')
                    ->label('Auditor')
                    ->relationship('auditor', 'no_sertifikat_auditor')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->person?->nama_lengkap ?? $record->no_sertifikat_auditor)
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('peran')
                    ->label('Peran')
                    ->options([
                        'KETUA_TIM' => 'Ketua Tim',
                        'ANGGOTA' => 'Anggota',
                    ])
                    ->default('ANGGOTA')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('auditor.person.nama_lengkap')->label('Nama Auditor'),
                TextColumn::make('peran')->label('Peran')->badge(),
            ])
            ->filters([
                //
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
