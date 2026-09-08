<?php

namespace App\Filament\Resources\TrxDosens\RelationManagers;

use App\Filament\Resources\TrxDosens\TrxDosenResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KetersediaansRelationManager extends RelationManager
{
    protected static string $relationship = 'ketersediaans';

    protected static ?string $relatedResource = TrxDosenResource::class;

    // UI/UX: Ubah judul tabel agar lebih mudah dipahami operator
    protected static ?string $title = 'Batas Waktu Mengajar (Whitelist)';

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('hari')
                    ->label('Hari Ketersediaan')
                    ->options([
                        'Senin' => 'Senin',
                        'Selasa' => 'Selasa',
                        'Rabu' => 'Rabu',
                        'Kamis' => 'Kamis',
                        'Jumat' => 'Jumat',
                        'Sabtu' => 'Sabtu',
                    ])
                    ->required()
                    ->columnSpan(2),

                TimePicker::make('jam_mulai')
                    ->label('Jam Mulai (Tersedia Dari)')
                    ->seconds(false)
                    ->required()
                    ->columnSpan(1),

                TimePicker::make('jam_selesai')
                    ->label('Jam Selesai (Hanya Sampai)')
                    ->seconds(false)
                    ->required()
                    ->after('jam_mulai') // UI/UX Validasi: Mencegah operator memasukkan jam mundur
                    ->columnSpan(1),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->description('PENTING: Jika tabel ini KOSONG, sistem menganggap Dosen bersedia mengajar 24/7. Namun jika diisi, mesin HANYA akan menjadwalkan Dosen ini pada hari dan jam yang terdaftar di bawah ini.')
            ->columns([
                TextColumn::make('hari')
                    ->label('Hari')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('jam_mulai')
                    ->label('Mulai Jam')
                    ->time('H:i') // Membuang format detik (00:00:00 -> 00:00)
                    ->sortable(),

                TextColumn::make('jam_selesai')
                    ->label('Sampai Jam')
                    ->time('H:i')
                    ->sortable(),
            ])
            ->filters([
                // Filter tidak terlalu dibutuhkan di sini karena data per dosen biasanya sedikit (1-5 baris)
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Waktu Ketersediaan')
                    ->icon('heroicon-o-plus-circle'),
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
