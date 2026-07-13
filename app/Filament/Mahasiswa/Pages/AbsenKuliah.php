<?php

namespace App\Filament\Mahasiswa\Pages;

use App\Services\PresensiMahasiswaService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\ValidationException;

class AbsenKuliah extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;

    protected static ?string $navigationLabel = 'Absen Kuliah';

    protected static ?string $title = 'Absen Kuliah';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.mahasiswa.pages.absen-kuliah';

    public ?string $tokenInput = null;

    public ?string $deviceFingerprint = null;


    public function submitToken(): void
    {
        $token = strtoupper(trim($this->tokenInput ?? ''));

        if (blank($token)) {
            Notification::make()->title('Masukkan token terlebih dahulu')->warning()->send();

            return;
        }

        try {
            app(PresensiMahasiswaService::class)->checkin(
                auth()->user(),
                $token,
                request()->ip(),
                $this->deviceFingerprint,
            );

            Notification::make()
                ->title('Presensi berhasil dicatat')
                ->success()
                ->send();

            $this->tokenInput = null;
        } catch (\Illuminate\Validation\ValidationException $e) {
            Notification::make()
                ->title('Gagal presensi')
                ->body(collect($e->errors())->flatten()->implode(' '))
                ->danger()
                ->send();
        }
    }
}
