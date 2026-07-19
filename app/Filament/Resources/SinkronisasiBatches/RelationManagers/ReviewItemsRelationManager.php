<?php

namespace App\Filament\Resources\SinkronisasiBatches\RelationManagers;

use App\Services\Keuangan\SinkronisasiTagihanService;
use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class ReviewItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviewItems';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('mahasiswa.nim')->label('NIM'),
                TextColumn::make('mahasiswa.person.nama_lengkap')->label('Nama'),
                TextColumn::make('komponenBiaya.nama_komponen')->label('Komponen'),
                TextColumn::make('nominal_existing')->label('Nominal Lama')->money('IDR'),
                TextColumn::make('nominal_skema_baru')->label('Nominal Skema Saat Ini')->money('IDR'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'PENDING' => 'warning',
                        'IN_PROGRESS' => 'info',
                        'RESOLVED' => 'success',
                        'IGNORED' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('adjustment.nomor_adjustment')
                    ->label('Adjustment')
                    ->placeholder('-')
                    ->url(fn($record) => $record->keuangan_adjustment_id
                        ? route('filament.admin.resources.keuangan-adjustments.view', ['record' => $record->keuangan_adjustment_id])
                        : null)
                    ->openUrlInNewTab(),
                TextColumn::make('resolvedBy.name')->label('Ditindaklanjuti Oleh')->placeholder('-'),
                TextColumn::make('resolved_at')->label('Waktu Resolusi')->dateTime('d M Y H:i')->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'PENDING' => 'Pending',
                    'IN_PROGRESS' => 'In Progress',
                    'RESOLVED' => 'Resolved',
                    'IGNORED' => 'Ignored',
                ])->default('PENDING'),
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                Action::make('ajukan_adjustment')
                    ->label('Ajukan ke Adjustment')
                    ->icon('heroicon-m-arrow-up-tray')
                    ->color('primary')
                    ->visible(fn($record) => $record->status === 'PENDING')
                    ->requiresConfirmation()
                    ->modalDescription('Ini akan membuat record Adjustment berstatus DRAFT di modul Adjustment. Nominal tagihan TIDAK berubah sampai Adjustment tersebut disetujui lewat alur approval yang sudah ada.')
                    ->action(function ($record) {
                        $this->prosesAjukan([$record->id]);
                    }),

                Action::make('abaikan')
                    ->label('Abaikan')
                    ->icon('heroicon-m-x-mark')
                    ->color('gray')
                    ->visible(fn($record) => $record->status === 'PENDING')
                    ->requiresConfirmation()
                    ->modalDescription('Temuan ini akan ditandai IGNORED dan tidak lagi diikutkan ke daftar review yang perlu ditindaklanjuti.')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('catatan')
                            ->label('Catatan (opsional)')
                            ->rows(2),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'IGNORED',
                            'resolved_by' => Auth::id(),
                            'resolved_at' => now(),
                            'catatan_resolusi' => $data['catatan'] ?? null,
                        ]);
                        Notification::make()->title('Temuan diabaikan')->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('ajukan_adjustment_bulk')
                        ->label('Ajukan Terpilih ke Adjustment')
                        ->icon('heroicon-m-arrow-up-tray')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalDescription('Membuat record Adjustment DRAFT untuk setiap baris terpilih yang masih berstatus PENDING. Baris yang sudah diproses admin lain sejak halaman ini dibuka akan otomatis dilewati dan dilaporkan, tidak akan diajukan dobel.')
                        ->action(function (Collection $records) {
                            $this->prosesAjukan($records->pluck('id')->all());
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    /**
     * Dipanggil baik dari aksi per-baris maupun bulk action. Locking dan
     * verifikasi status ulang ditegakkan di dalam
     * SinkronisasiTagihanService::ajukanKeAdjustment() (bukan di sini),
     * supaya perilakunya konsisten dari jalur mana pun aksi ini dipicu.
     */
    private function prosesAjukan(array $ids): void
    {
        $service = app(SinkronisasiTagihanService::class);
        $hasil = $service->ajukanKeAdjustment($ids, Auth::id());

        $jumlahDibuat = count($hasil['dibuat']);
        $jumlahDilewati = count($hasil['dilewati']);

        if ($jumlahDibuat > 0) {
            Notification::make()
                ->title("{$jumlahDibuat} Adjustment berhasil dibuat")
                ->success()
                ->send();
        }

        if ($jumlahDilewati > 0) {
            Notification::make()
                ->title("{$jumlahDilewati} baris dilewati")
                ->body('Baris tersebut sudah diproses lebih dulu (kemungkinan oleh admin lain) sejak halaman ini dimuat.')
                ->warning()
                ->send();
        }
    }
}
