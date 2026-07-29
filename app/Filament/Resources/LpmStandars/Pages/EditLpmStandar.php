<?php

namespace App\Filament\Resources\LpmStandars\Pages;

use App\Filament\Resources\LpmStandars\LpmStandarResource;
use App\Models\LpmRiwayatPeningkatan;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\EditRecord;

class EditLpmStandar extends EditRecord
{
    protected static string $resource = LpmStandarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('tingkatkanStandar')
                ->label('Tingkatkan Standar')
                ->icon('heroicon-o-arrow-trending-up')
                ->color('warning')
                ->schema([
                    Textarea::make('ringkasan_perubahan')
                        ->label('Ringkasan Perubahan')
                        ->required()
                        ->rows(3)
                        ->helperText('Apa yang berubah dari standar versi sebelumnya ke versi baru ini.'),
                    Select::make('dasar_peningkatan')
                        ->label('Dasar Peningkatan')
                        ->options([
                            'HASIL_AMI' => 'Hasil Audit Mutu Internal',
                            'HASIL_MONEV' => 'Hasil Monitoring & Evaluasi',
                            'TINJAUAN_MANAJEMEN' => 'Tinjauan Manajemen',
                            'LAINNYA' => 'Lainnya',
                        ])
                        ->required(),
                    Select::make('disetujui_oleh_person_id')
                        ->label('Disetujui Oleh')
                        ->relationship('disetujuiOleh', 'nama_lengkap')
                        ->searchable()
                        ->preload(),
                ])
                ->action(function (array $data): void {
                    /** @var \App\Models\LpmStandar $record */
                    $record = $this->record;
                    $versiLama = (int) $record->versi;
                    $versiBaru = $versiLama + 1;

                    LpmRiwayatPeningkatan::create([
                        'standar_id' => $record->id,
                        'versi_lama' => $versiLama,
                        'versi_baru' => $versiBaru,
                        'ringkasan_perubahan' => $data['ringkasan_perubahan'],
                        'dasar_peningkatan' => $data['dasar_peningkatan'],
                        'disetujui_oleh_person_id' => $data['disetujui_oleh_person_id'] ?? null,
                        'tanggal' => now(),
                    ]);

                    $record->update(['versi' => $versiBaru]);

                    $this->refreshFormData(['versi']);
                }),
            DeleteAction::make(),
        ];
    }
}
