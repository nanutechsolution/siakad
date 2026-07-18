<?php

namespace App\Filament\Resources\MasterKurikulums\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class KurikulumKomponenNilaiRelationManager extends RelationManager
{
    protected static string $relationship = 'kurikulumKomponenNilais';

    protected static ?string $title = 'Komponen Penilaian Standar Kurikulum';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('komponen_id')
                    ->label('Komponen Nilai')
                    // 1. Hubungkan langsung ke nama fungsi relasi BelongsTo di Model Jembatan
                    // Parameter kedua adalah nama kolom yang ingin ditampilkan ('nama_komponen')
                    ->relationship('masterKomponen', 'nama_komponen')

                    // 2. Tambahkan form popup untuk membuat data master baru di ref_komponen_nilai
                    ->createOptionForm([
                        Grid::make(1)
                            ->schema([
                                TextInput::make('nama_komponen')
                                    ->label('Nama Master Komponen Baru')
                                    ->placeholder('Contoh: Keaktifan, Tugas Mandiri, Project')
                                    ->required()
                                    ->maxLength(255)
                                    // Otomatis mengisi kolom slug secara real-time ketika user mengetik nama
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(
                                        fn(string $operation, $state, Set $set) =>
                                        $set('slug', Str::slug($state))
                                    ),

                                TextInput::make('slug')
                                    ->label('Slug (Sistem)')
                                    ->required()
                                    ->unique('ref_komponen_nilai', 'slug') // Validasi unik agar tidak crash di tingkat DB
                                    ->disabled() // Dibuat disabled agar user tidak perlu repot mengetik manual
                                    ->dehydrated(), // Tetap dikirim ke database saat proses simpan meskipun statusnya disabled

                                Toggle::make('is_active')
                                    ->label('Status Aktif')
                                    ->default(true)
                                    ->required(),
                            ])
                    ])
                    ->required()
                    ->searchable()
                    ->preload(),

                // Input bobot default kurikulum
                TextInput::make('bobot_persen')
                    ->label('Bobot Default (%)')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->maxValue(100)
                    ->suffix('%'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('masterKomponen.nama_komponen')
                    ->label('Nama Komponen')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('bobot_persen')
                    ->label('Bobot Standar')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('success')
                    ->suffix('%'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Komponen Standar')
                    ->visible(fn() => auth()->user()->can('CreateKomponenNilai'))
                    ->icon('heroicon-m-plus')
                    ->before(function (RelationManager $livewire, $state) {
                        if (blank($state) || ! isset($state['bobot_persen'])) {
                            return;
                        }

                        $currentTotal = $livewire->getRelationship()->sum('bobot_persen');
                        $inputBobot = (int) $state['bobot_persen'];
                        $newTotal = $currentTotal + $inputBobot;

                        if ($newTotal > 100) {
                            \Filament\Notifications\Notification::make()
                                ->title('Total Bobot Melebihi 100%')
                                ->body("Total bobot standar kurikulum saat ini {$currentTotal}%. Tambahan {$inputBobot}% membuat total menjadi {$newTotal}%.")
                                ->danger()
                                ->send();

                            $livewire->halt();
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn() => auth()->user()->can('UpdateKomponenNilai'))
                    ->color('warning')
                    ->before(function (RelationManager $livewire, $record, $state) {
                        // 1. TAMBAHKAN VALIDASI: Cek apakah $state null atau tidak memiliki key 'bobot_persen'
                        if (blank($state) || ! isset($state['bobot_persen'])) {
                            return; // Lewati validasi jika state kosong (misal saat dipicu dari modal cilik ref_komponen_nilai)
                        }

                        // Hitung total dengan mengecualikan baris yang sedang diedit
                        $currentTotal = $livewire->getRelationship()
                            ->where('id', '!=', $record->id)
                            ->sum('bobot_persen');

                        // Ambil nilai bobot baru dan pastikan diconvert ke integer/float secara aman
                        $inputBobot = (int) $state['bobot_persen'];
                        $newTotal = $currentTotal + $inputBobot;

                        if ($newTotal > 100) {
                            \Filament\Notifications\Notification::make()
                                ->title('Total Bobot Melebihi 100%')
                                ->body("Total bobot komponen lain adalah {$currentTotal}%. Perubahan menjadi {$inputBobot}% membuat total menjadi {$newTotal}%.")
                                ->danger()
                                ->send();

                            $livewire->halt(); // Batalkan proses update
                        }
                    }),
                DeleteAction::make()->visible(fn() => auth()->user()->can('DeleteKomponenNilai')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(fn() => auth()->user()->can('DeleteKomponenNilai')),
                ]),
            ]);
    }
}
