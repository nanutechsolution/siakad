<?php

namespace App\Filament\Resources\Kelas\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DosenWaliRelationManager extends RelationManager
{
    protected static string $relationship = 'dosens';
    protected static ?string $title = 'Dosen Wali';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('dosen_id')
                    ->label('Pilih Dosen')
                    ->options(
                        \App\Models\TrxDosen::query()
                            ->join('ref_person', 'trx_dosen.person_id', '=', 'ref_person.id')
                            ->whereNotNull('ref_person.nama_lengkap')
                            ->get()
                            ->mapWithKeys(function ($item) {
                                return [$item->id => "{$item->nama_lengkap} (NIDN: {$item->nidn})"];
                            })
                    )
                    ->searchable()
                    ->required(),
                Toggle::make('is_primary')
                    ->label('Dosen Wali Utama')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('person.nama_lengkap')->label('Nama Dosen'),
                TextColumn::make('nidn')->label('NIDN'),
                IconColumn::make('pivot.is_primary')
                    ->label('Wali Utama')
                    ->boolean(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->schema(fn($action) => [
                        $action->getRecordSelect(),
                        Toggle::make('is_primary')->default(true),
                    ]),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
