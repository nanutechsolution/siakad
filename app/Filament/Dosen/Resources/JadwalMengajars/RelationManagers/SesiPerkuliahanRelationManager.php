<?php

namespace App\Filament\Dosen\Resources\JadwalMengajars\RelationManagers;

use App\Enums\StatusSesiEnum;
use App\Filament\Dosen\Resources\JadwalMengajars\JadwalMengajarResource;
use App\Models\PerkuliahanSesi;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SesiPerkuliahanRelationManager extends RelationManager
{
    protected static string $relationship = 'sesiPerkuliahan';
    /**
     * Override default behavior Filament di ViewRecord Page.
     * Paksa Relation Manager ini agar tetap bisa melakukan aksi Create/Edit/Delete.
     */
    public function isReadOnly(): bool
    {
        return false;
    }

    /**
     * Izinkan dosen melihat daftar jadwal (jika belum ada)
     */
    public  function canViewAny(): bool
    {
        return true;
    }

    /**
     * Izinkan dosen melihat detail record (Wajib agar ViewAction muncul)
     */
    public  function canView($record): bool
    {
        // Karena ini Dosen Panel, asumsikan dosen boleh melihat detail jadwalnya sendiri
        return true;
    }

    public  function canCreate(): bool
    {
        return true;
    }

    public  function canEdit($record): bool
    {
        return true;
    }
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('pertemuan_ke')
                    ->label('Pertemuan Ke-')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->maxValue(16)
                    ->unique(
                        modifyRuleUsing: function ($rule, callable $get) {
                            return $rule->where('jadwal_kuliah_id', $this->getOwnerRecord()->id);
                        },
                        ignoreRecord: true
                    ),
                DateTimePicker::make('waktu_mulai_rencana')
                    ->label('Rencana Waktu Mulai')
                    ->required(),
                Select::make('metode_validasi')
                    ->label('Metode Presensi')
                    ->options([
                        'QR' => 'QR Code',
                        'PIN' => 'PIN / Token',
                        'MANUAL' => 'Panggilan Manual (Dosen)',
                    ])
                    ->default('QR')
                    ->required(),
                RichEditor::make('materi_kuliah')
                    ->label('Rencana Materi / Topik')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('pertemuan_ke', 'asc')
            ->columns([
                TextColumn::make('pertemuan_ke')
                    ->label('Pertemuan')
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn($state) => "Ke-{$state}"),
                TextColumn::make('waktu_mulai_rencana')
                    ->label('Rencana Mulai')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('status_sesi')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('token_sesi')
                    ->label('Token Presensi')
                    ->copyable()
                    ->placeholder('-'),
            ])
            ->headerActions([
                CreateAction::make()->label('Buat Rencana Sesi'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('buka_sesi')
                    ->label('Buka Sesi')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status_sesi === StatusSesiEnum::TERJADWAL)
                    ->action(function ($record) {
                        $record->update([
                            'status_sesi' => StatusSesiEnum::DIBUKA,
                            'waktu_mulai_realisasi' => now(),
                            'token_sesi' => strtoupper(Str::random(6)),
                        ]);
                    }),
                Action::make('tutup_sesi')
                    ->label('Tutup Sesi')

                    ->icon('heroicon-o-stop')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status_sesi === StatusSesiEnum::DIBUKA)
                    ->schema([
                        RichEditor::make('catatan_dosen')
                            ->label('Catatan Jurnal Dosen (Realisasi Materi)')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status_sesi' => StatusSesiEnum::SELESAI,
                            'waktu_selesai_realisasi' => now(),
                            'catatan_dosen' => $data['catatan_dosen'],
                        ]);
                    }),
                Action::make('presensi')
                    ->label('Absensi')
                    ->icon('heroicon-o-users')
                    ->color('info')
                    ->url(fn(PerkuliahanSesi $record): string => JadwalMengajarResource::getUrl('presensi', [
                        'record' => $record->jadwal_kuliah_id,
                        'sesiId' => $record->id
                    ]))
                    // Tombol hanya bisa diakses kalau sesi sudah dibuka atau selesai
                    ->visible(fn(PerkuliahanSesi $record) => in_array($record->status_sesi, [StatusSesiEnum::DIBUKA, StatusSesiEnum::SELESAI])),
            ])
            ->filters([])
            ->headerActions([
                CreateAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
