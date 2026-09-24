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
                    // Kelas pernah/sedang punya anggota -> jangan dihapus.
                    // Mahasiswa KRS juga menolak FK kelas (restrict), jadi
                    // amankan riwayat keanggotaan + dosen wali sekaligus.
                    $adaRiwayat = $record->mahasiswaKelas()->exists();
                    $adaDosenWali = $record->pembimbingAkademik()->exists();

                    if ($adaRiwayat || $adaDosenWali) {
                        $pesan = 'Kelas tidak dapat dihapus karena masih memiliki ';
                        if ($adaRiwayat && $adaDosenWali) {
                            $pesan .= 'anggota (aktif/riwayat) dan dosen wali yang terikat.';
                        } elseif ($adaRiwayat) {
                            $pesan .= 'riwayat keanggotaan mahasiswa di dalamnya.';
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
