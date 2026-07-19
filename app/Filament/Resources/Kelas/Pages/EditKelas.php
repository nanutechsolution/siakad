<?php

namespace App\Filament\Resources\Kelas\Pages;

use App\Filament\Resources\Kelas\KelasResource;
use App\Models\Kelas;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditKelas extends EditRecord
{
    protected static string $resource = KelasResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (DeleteAction $action, Kelas $record) {
                    // 1. Cek mahasiswa aktif
                    $hasMahasiswaAktif = $record->mahasiswaKelasAktif()->exists();

                    /**
                     * 2. Cek relasi dosen wali
                     * PENTING: Pastikan nama relasi di bawah ini ('dosenWali') sesuai dengan 
                     * nama method relasi yang tertulis di dalam file App\Models\Kelas.php
                     */
                    $namaRelasiDosenWali = 'dosenWali'; // Jalur alternatif jika namanya 'kelasDosenWali' atau 'dosenWalis'
                    $hasDosenWali = method_exists($record, $namaRelasiDosenWali) ? $record->{$namaRelasiDosenWali}()->exists() : false;

                    // 3. Gabungkan kondisi dalam satu IF
                    if ($hasMahasiswaAktif || $hasDosenWali) {
                        // Tentukan pesan error yang spesifik agar user tahu penyebabnya
                        $pesan = 'Kelas tidak dapat dihapus karena masih memiliki ';
                        if ($hasMahasiswaAktif && $hasDosenWali) {
                            $pesan .= 'mahasiswa aktif dan dosen wali yang terikat.';
                        } elseif ($hasMahasiswaAktif) {
                            $pesan .= 'mahasiswa aktif di dalamnya.';
                        } else {
                            $pesan .= 'dosen wali yang terikat.';
                        }

                        Notification::make()
                            ->title('Gagal Menghapus Kelas')
                            ->body($pesan . ' Selesaikan dependensi data terlebih dahulu.')
                            ->danger()
                            ->persistent()
                            ->send();

                        // Batalkan proses hapus ke database
                        $action->cancel();
                    }
                })
        ];
    }
}
