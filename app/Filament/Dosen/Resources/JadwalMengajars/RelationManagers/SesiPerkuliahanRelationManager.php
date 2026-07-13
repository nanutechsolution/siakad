<?php

namespace App\Filament\Dosen\Resources\JadwalMengajars\RelationManagers;

use App\Enums\StatusSesiPerkuliahan;
use App\Filament\Dosen\Resources\JadwalMengajars\JadwalMengajarResource;
use App\Models\PerkuliahanSesi;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
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

    protected static ?string $relatedResource = JadwalMengajarResource::class;
    public function isReadOnly(): bool
    {
        return false;
    }

    public function canViewAny(): bool
    {
        return true;
    }

    public function canView($record): bool
    {
        return true;
    }

    public function canCreate(): bool
    {
        return true;
    }

    public function canEdit($record): bool
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
                        modifyRuleUsing: fn($rule) => $rule->where('jadwal_kuliah_id', $this->getOwnerRecord()->id),
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
        $this->rotasiTokenKedaluwarsa();
        return $table
            ->poll('20s')
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
                    ->visible(fn($record) => $record->status_sesi === StatusSesiPerkuliahan::Terjadwal)
                    ->action(function ($record): void {
                        $record->update([
                            'status_sesi' => StatusSesiPerkuliahan::Dibuka,
                            'waktu_mulai_realisasi' => now(),
                            'token_sesi' => strtoupper(Str::random(6)),
                            'token_generated_at' => now(),
                        ]);

                        $this->seedAbsensiAwal($record);
                    }),
                Action::make('tutup_sesi')
                    ->label('Tutup Sesi')
                    ->icon('heroicon-o-stop')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status_sesi === StatusSesiPerkuliahan::Dibuka)
                    ->schema([
                        RichEditor::make('catatan_dosen')
                            ->label('Catatan Jurnal Dosen (Realisasi Materi)')
                            ->required(),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'status_sesi' => StatusSesiPerkuliahan::Selesai,
                            'waktu_selesai_realisasi' => now(),
                            'catatan_dosen' => $data['catatan_dosen'],
                        ]);
                    }),
                Action::make('presensi')
                    ->label('Absensi')
                    ->icon('heroicon-o-users')
                    ->color('info')
                    ->url(fn(PerkuliahanSesi $record): string => JadwalMengajarResource::getUrl('presensi-sesi', [
                        'record' => $record->jadwal_kuliah_id,
                        'sesiId' => $record->id,
                    ]))
                    ->visible(fn(PerkuliahanSesi $record) => in_array($record->status_sesi, [
                        StatusSesiPerkuliahan::Dibuka,
                        StatusSesiPerkuliahan::Selesai,
                    ])),
            ])
            ->filters([])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
    protected function rotasiTokenKedaluwarsa(): void
    {
        \App\Models\PerkuliahanSesi::where('jadwal_kuliah_id', $this->getOwnerRecord()->id)
            ->where('status_sesi', \App\Enums\StatusSesiPerkuliahan::Dibuka->value)
            ->where(function ($q) {
                $q->whereNull('token_generated_at')
                    ->orWhere('token_generated_at', '<=', now()->subSeconds(20));
            })
            ->get()
            ->each(fn($sesi) => $sesi->update([
                'token_sesi' => strtoupper(\Illuminate\Support\Str::random(6)),
                'token_generated_at' => now(),
            ]));
    }
    protected function seedAbsensiAwal(PerkuliahanSesi $sesi): void
    {
        $krsDetailIds = \App\Models\KrsDetail::where('jadwal_kuliah_id', $sesi->jadwal_kuliah_id)
            ->pluck('id');

        foreach ($krsDetailIds as $krsDetailId) {
            \App\Models\PerkuliahanAbsensi::firstOrCreate(
                [
                    'perkuliahan_sesi_id' => $sesi->id,
                    'krs_detail_id' => $krsDetailId,
                ],
                ['status_kehadiran' => \App\Enums\StatusKehadiran::Alpa->value]
            );
        }
    }
}
