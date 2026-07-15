<?php

namespace App\Filament\Resources\DosenDokumens\Tables;

use App\Models\DosenDokumen;
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
use Illuminate\Support\Facades\Storage;

class DosenDokumensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->latest())
            ->columns([
                TextColumn::make('dosen.nidn')->label('NIDN')->searchable(),
                TextColumn::make('dosen.person.nama_lengkap')->label('Nama Dosen')->searchable(),
                TextColumn::make('jenisDokumen.nama_dokumen')->label('Jenis Dokumen'),

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

                TextColumn::make('created_at')->label('Diupload')->dateTime('d M Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'])
                    ->default('pending'),
            ])
            ->recordActions([
                Action::make('lihat_file')
                    ->label('Lihat File')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn(DosenDokumen $record) => Storage::url($record->file_path))
                    ->openUrlInNewTab(),

                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn(DosenDokumen $record) => $record->status === 'pending')
                    ->action(function (DosenDokumen $record) {
                        $record->approve(Auth::user());
                        \Filament\Notifications\Notification::make()->title('Dokumen disetujui')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->schema([Textarea::make('rejection_note')->label('Alasan Penolakan')->required()->rows(3)])
                    ->visible(fn(DosenDokumen $record) => $record->status === 'pending')
                    ->action(function (DosenDokumen $record, array $data) {
                        $record->reject(Auth::user(), $data['rejection_note']);
                        \Filament\Notifications\Notification::make()->title('Dokumen ditolak')->warning()->send();
                    }),
            ]);
    }
}
