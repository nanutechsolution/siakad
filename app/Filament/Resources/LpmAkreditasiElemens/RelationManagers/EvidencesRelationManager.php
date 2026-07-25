<?php

namespace App\Filament\Resources\LpmAkreditasiElemens\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
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
    protected static ?string $title = 'Evidence / Bukti';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('indikator_id')
                    ->label('Indikator Terkait (opsional)')
                    ->relationship('indikator', 'deskripsi')
                    ->searchable(),
                FileUpload::make('file_path')
                    ->label('File Bukti')
                    ->directory('lpm/akreditasi/evidences')
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
                TextColumn::make('indikator.deskripsi')->label('Indikator Terkait')->limit(40)->toggleable(),
                TextColumn::make('uploadedBy.nama_lengkap')->label('Diunggah Oleh'),
                TextColumn::make('created_at')->label('Tanggal')->dateTime('d/m/Y H:i'),
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
            ])->defaultSort('created_at', 'desc');
    }
}
