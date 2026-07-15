<?php

namespace App\Filament\Resources\ProfileChangeRequests\Tables;

use App\Models\ProfileChangeRequest;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ProfileChangeRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->latest())
            ->columns([
                TextColumn::make('mahasiswa.nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('mahasiswa.person.nama_lengkap')
                    ->label('Nama Mahasiswa')
                    ->searchable(),

                TextColumn::make('field_name')
                    ->label('Field')
                    ->formatStateUsing(fn(string $state) => self::fieldLabels()[$state] ?? $state)
                    ->badge()
                    ->color('gray'),

                TextColumn::make('old_value')
                    ->label('Nilai Lama')
                    ->limit(30),

                TextColumn::make('new_value')
                    ->label('Nilai Baru Diajukan')
                    ->limit(30)
                    ->weight('bold'),

                TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('reviewer.name')
                    ->label('Diverifikasi Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->default('pending'),
            ])
            ->recordActions([
                Action::make('lihat_lampiran')
                    ->label('Lampiran')
                    ->icon('heroicon-o-paper-clip')
                    ->color('gray')
                    ->url(fn(ProfileChangeRequest $record) => $record->attachment_path
                        ? \Illuminate\Support\Facades\Storage::url($record->attachment_path)
                        : null)
                    ->openUrlInNewTab()
                    ->visible(fn(ProfileChangeRequest $record) => filled($record->attachment_path)),
                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui perubahan data?')
                    ->modalDescription(fn(ProfileChangeRequest $record) => sprintf(
                        'Data "%s" milik %s akan diubah dari "%s" menjadi "%s". Perubahan langsung diterapkan ke data resmi mahasiswa.',
                        self::fieldLabels()[$record->field_name] ?? $record->field_name,
                        $record->mahasiswa->person->nama_lengkap ?? '-',
                        $record->old_value ?? '-',
                        $record->new_value
                    ))
                    ->visible(fn(ProfileChangeRequest $record) => $record->status === 'pending')
                    ->action(function (ProfileChangeRequest $record) {
                        try {
                            $record->approve(Auth::user());

                            Notification::make()
                                ->title('Perubahan data disetujui')
                                ->success()
                                ->send();
                        } catch (UniqueConstraintViolationException $e) {

                            Notification::make()
                                ->title('Gagal menyetujui perubahan')
                                ->body('Data yang akan disimpan sudah digunakan oleh data lain. Misalnya NIK sudah terdaftar.')
                                ->danger()
                                ->persistent()
                                ->send();
                        } catch (Throwable $e) {

                            report($e); // tetap masuk ke laravel.log

                            Notification::make()
                                ->title('Terjadi kesalahan')
                                ->body($e->getMessage()) // bisa diganti pesan yang lebih umum jika di production
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->schema([
                        Textarea::make('rejection_note')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->visible(fn(ProfileChangeRequest $record) => $record->status === 'pending')
                    ->action(function (ProfileChangeRequest $record, array $data) {
                        $record->reject(Auth::user(), $data['rejection_note']);

                        \Filament\Notifications\Notification::make()
                            ->title('Pengajuan ditolak')
                            ->warning()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Label field_name yang tersimpan (kolom teknis di ref_person)
     * supaya enak dibaca admin.
     */
    public static function fieldLabels(): array
    {
        return [
            'nama_lengkap' => 'Nama Lengkap',
            'nik' => 'NIK',
            'tanggal_lahir' => 'Tanggal Lahir',
            'tempat_lahir' => 'Tempat Lahir',
            'jenis_kelamin' => 'Jenis Kelamin',
        ];
    }
}
