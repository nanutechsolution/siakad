<?php

namespace App\Filament\Resources\Kelas\RelationManagers;

use App\Models\TrxDosen;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class DosenWaliRelationManager extends RelationManager
{
    protected static string $relationship = 'kelasDosenWalis';

    protected static ?string $title = 'Dosen Wali';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('dosen_id')
                    ->label('Dosen')
                    ->relationship(
                        name: 'dosen',
                        titleAttribute: 'nidn'
                    )
                    ->getOptionLabelFromRecordUsing(
                        fn(TrxDosen $record) =>
                        "{$record->person->nama_lengkap} ({$record->nidn})"
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('Pilih dosen yang akan menjadi dosen wali kelas.'),

                Toggle::make('is_primary')
                    ->label('Dosen Wali Utama')
                    ->helperText(
                        'Hanya satu dosen wali utama diperbolehkan pada setiap kelas.'
                    )
                    ->default(false),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Daftar Dosen Wali')
            ->description('Kelola dosen wali yang bertanggung jawab terhadap kelas ini.')

            ->columns([

                Tables\Columns\TextColumn::make('dosen.person.nama_dengan_gelar')
                    ->label('Nama Dosen')
                    ->state(fn($record) => $record->dosen->person->nama_dengan_gelar)
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('dosen.person', function ($q) use ($search) {
                            $q->where('nama_lengkap', 'like', "%{$search}%");
                        });
                    }),
                Tables\Columns\TextColumn::make('dosen.nidn')
                    ->label('NIDN')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_primary')
                    ->label('Wali Utama')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ditambahkan')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),

            ])

            ->defaultSort('is_primary', 'desc')

            ->headerActions([

                CreateAction::make()
                    ->label('Tambah Dosen Wali')
                    ->icon('heroicon-o-plus')

                    ->using(function (array $data) {

                        return DB::transaction(function () use ($data) {

                            $owner = $this->getOwnerRecord();

                            // Jangan boleh dosen yang sama dua kali
                            if (
                                $owner->kelasDosenWalis()
                                ->where('dosen_id', $data['dosen_id'])
                                ->exists()
                            ) {

                                Notification::make()
                                    ->danger()
                                    ->title('Dosen sudah terdaftar')
                                    ->body('Dosen tersebut sudah menjadi dosen wali kelas ini.')
                                    ->send();

                                return null;
                            }

                            // Jika dijadikan utama,
                            // nonaktifkan utama sebelumnya
                            if ($data['is_primary']) {

                                $owner->kelasDosenWalis()
                                    ->update([
                                        'is_primary' => false,
                                    ]);
                            }

                            return $owner
                                ->kelasDosenWalis()
                                ->create($data);
                        });
                    }),

            ])

            ->recordActions([

                EditAction::make()

                    ->using(function ($record, array $data) {

                        return DB::transaction(function () use ($record, $data) {

                            $owner = $this->getOwnerRecord();

                            $exists = $owner->kelasDosenWalis()
                                ->where('dosen_id', $data['dosen_id'])
                                ->where('id', '!=', $record->id)
                                ->exists();

                            if ($exists) {

                                Notification::make()
                                    ->danger()
                                    ->title('Dosen sudah terdaftar')
                                    ->body('Dosen tersebut sudah menjadi dosen wali.')
                                    ->send();

                                return $record;
                            }

                            if ($data['is_primary']) {

                                $owner->kelasDosenWalis()
                                    ->where('id', '!=', $record->id)
                                    ->update([
                                        'is_primary' => false,
                                    ]);
                            }

                            $record->update($data);

                            return $record;
                        });
                    }),
                DeleteAction::make()
                    ->requiresConfirmation(),

            ])

            ->toolbarActions([

                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                ]),

            ]);
    }
}
