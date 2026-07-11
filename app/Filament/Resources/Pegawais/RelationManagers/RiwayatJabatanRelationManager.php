<?php

namespace App\Filament\Resources\Pegawais\RelationManagers;

use App\Services\HR\PegawaiJabatanService;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RiwayatJabatanRelationManager extends RelationManager
{
    protected static string $relationship = 'riwayatJabatan';
    protected static ?string $title = 'Riwayat Jabatan';
    protected static string|BackedEnum|null $icon = 'heroicon-o-briefcase';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('jabatan_id')
                    ->label('Jabatan')
                    ->relationship('jabatan', 'nama_jabatan')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull(),
                Select::make('fakultas_id')
                    ->label('Unit Fakultas (Opsional)')
                    ->relationship('fakultas', 'nama_fakultas')
                    ->searchable()
                    ->preload(),
                Select::make('prodi_id')
                    ->label('Unit Program Studi (Opsional)')
                    ->relationship('prodi', 'nama_prodi')
                    ->searchable()
                    ->preload(),
                DatePicker::make('tanggal_mulai')
                    ->label('Tanggal Mulai Menjabat')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                DatePicker::make('tanggal_selesai')
                    ->label('Tanggal Akhir Jabatan')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->afterOrEqual('tanggal_mulai')
                    ->placeholder('Kosongkan jika masih menjabat'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('jabatan.nama_jabatan')
            ->columns([
                TextColumn::make('jabatan.nama_jabatan')
                    ->label('Jabatan')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('jabatan.jenis')
                    ->label('Kategori')
                    ->badge(),
                TextColumn::make('fakultas.nama_fakultas')
                    ->label('Fakultas')
                    ->placeholder('-'),
                TextColumn::make('prodi.nama_prodi')
                    ->label('Program Studi')
                    ->placeholder('-'),
                TextColumn::make('tanggal_mulai')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('tanggal_selesai')
                    ->label('Selesai')
                    ->date('d M Y')
                    ->placeholder('Masih Menjabat')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('aktif_saja')
                    ->label('Hanya Jabatan Aktif')
                    ->query(fn($query) => $query->whereNull('tanggal_selesai')),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label("Penugasan Jabatan")
                    ->using(function (array $data, RelationManager $livewire): Model {
                        return DB::transaction(function () use ($data, $livewire) {
                            // Gunakan relasi dari parent (owner) untuk create data,
                            // sehingga 'person_id' otomatis terisi sesuai ID Pegawai yang sedang dibuka.
                            $record = $livewire->getRelationship()->create($data);

                            // Jalankan service validasi bisnis
                            app(PegawaiJabatanService::class)->periksaIndikasiRangkapStruktural($record);

                            return $record;
                        });
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->using(function (Model $record, array $data): Model {
                        return DB::transaction(function () use ($record, $data) {
                            $record->update($data);
                            app(PegawaiJabatanService::class)->periksaIndikasiRangkapStruktural($record);
                            return $record;
                        });
                    }),
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
