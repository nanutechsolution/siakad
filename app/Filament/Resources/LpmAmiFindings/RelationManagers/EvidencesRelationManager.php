<?php

namespace App\Filament\Resources\LpmAmiFindings\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EvidencesRelationManager extends RelationManager
{
    protected static string $relationship = 'evidences';

    protected static ?string $title = 'Bukti Audit (Evidence)';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('file_path')
                    ->label('File Bukti')
                    ->directory('lpm/ami/evidences')
                    ->required(),
                TextInput::make('keterangan')
                    ->label('Keterangan')
                    ->maxLength(255),
                Select::make('uploaded_by_person_id')
                    ->label('Diunggah Oleh')
                    ->relationship('uploadedBy', 'nama_lengkap')
                    ->searchable()
                    ->preload(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('keterangan')
            ->columns([
                TextColumn::make('keterangan')->label('Keterangan')->wrap(),
                TextColumn::make('uploadedBy.nama_lengkap')->label('Diunggah Oleh'),
                TextColumn::make('created_at')->label('Tanggal Unggah')->dateTime('d/m/Y H:i'),
            ])
            ->defaultSort('created_at', 'desc')
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
