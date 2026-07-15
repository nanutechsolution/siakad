<?php

namespace App\Filament\Resources\DosenProfileChangeRequests\Tables;

use App\Models\DosenProfileChangeRequest;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DosenProfileChangeRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->latest())
            ->columns([
                TextColumn::make('dosen.nidn')
                    ->label('NIDN')
                    ->searchable(),

                TextColumn::make('dosen.person.nama_lengkap')
                    ->label('Nama Dosen')
                    ->searchable(),

                TextColumn::make('field_name')
                    ->label('Field')
                    ->formatStateUsing(fn(string $state) => self::fieldLabels()[$state] ?? $state)
                    ->badge()
                    ->color('gray'),

                TextColumn::make('old_value')->label('Nilai Lama')->limit(30),
                TextColumn::make('new_value')->label('Nilai Baru')->limit(30)->weight('bold'),

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

                TextColumn::make('created_at')->label('Diajukan')->dateTime('d M Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'])
                    ->default('pending'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn(DosenProfileChangeRequest $record) => $record->status === 'pending')
                    ->action(function (DosenProfileChangeRequest $record) {
                        $record->approve(Auth::user());
                        \Filament\Notifications\Notification::make()->title('Perubahan data disetujui')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([Textarea::make('rejection_note')->label('Alasan Penolakan')->required()->rows(3)])
                    ->visible(fn(DosenProfileChangeRequest $record) => $record->status === 'pending')
                    ->action(function (DosenProfileChangeRequest $record, array $data) {
                        $record->reject(Auth::user(), $data['rejection_note']);
                        \Filament\Notifications\Notification::make()->title('Pengajuan ditolak')->warning()->send();
                    }),
            ]);
    }

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
