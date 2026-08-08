<?php

namespace App\Filament\Resources\TrxDosens\Pages;

use App\Filament\Resources\TrxDosens\TrxDosenResource;
use App\Models\TrxPersonJabatan;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditTrxDosen extends EditRecord
{
    protected static string $resource = TrxDosenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        $this->validateRiwayatJabatan();
    }

    protected function validateRiwayatJabatan(): void
    {
        $items = $this->data['atribusiJabatan'] ?? [];

        foreach ($items as $index => $item) {
            $this->validateJabatanItem(
                item: $item,
                index: $index,
                currentRecordId: $this->record?->getKey(),
            );
        }

        $this->validateRepeaterOverlap($items);
    }
    protected function validateJabatanItem(array $item, int $index, mixed $currentRecordId = null,): void
    {
        $jabatanId = $item['jabatan_id'] ?? null;
        $fakultasId = $item['fakultas_id'] ?? null;
        $prodiId = $item['prodi_id'] ?? null;
        $tanggalMulai = $item['tanggal_mulai'] ?? null;
        $tanggalSelesai = $item['tanggal_selesai'] ?? null;
        if (blank($jabatanId) || blank($tanggalMulai)) {
            return;
        }
        if (filled($tanggalSelesai) && $tanggalSelesai < $tanggalMulai) {
            throw ValidationException::withMessages(["atribusiJabatan.{$index}.tanggal_selesai" => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',]);
        }
        $query = TrxPersonJabatan::query()->where('jabatan_id', $jabatanId)->where(function ($query) use ($fakultasId) {
            if (is_null($fakultasId)) {
                $query->whereNull('fakultas_id');
            } else {
                $query->where('fakultas_id', $fakultasId);
            }
        })->where(function ($query) use ($prodiId) {
            if (is_null($prodiId)) {
                $query->whereNull('prodi_id');
            } else {
                $query->where('prodi_id', $prodiId);
            }
        })->whereHas('person', function ($query) {
            // person akan ditentukan dari parent record
        });

        /* * Untuk Repeater relationship, person_id berasal dari record parent. */
        $personId = $this->record?->person_id ?? $this->data['person_id'] ?? null;
        if (blank($personId)) {
            return;
        }
        $query->where('person_id', $personId);
        /* * Saat Edit, jangan membandingkan record jabatan * dengan dirinya sendiri. */
        if ($currentRecordId) {
            $query->where('id', '!=', $currentRecordId);
        }

        /* * Logika overlap: Existing mulai <= New selesai DAN Existing selesai >= New mulai NULL tanggal_selesai dianggap tidak terbatas. */
        $query->where('tanggal_mulai', '<=', $tanggalSelesai ?? '9999-12-31')->where(function ($query) use ($tanggalMulai) {
            $query->whereNull('tanggal_selesai')->orWhere('tanggal_selesai', '>=', $tanggalMulai);
        });
        if ($query->exists()) {
            throw ValidationException::withMessages(["atribusiJabatan.{$index}.tanggal_mulai" => 'Periode jabatan bertabrakan dengan riwayat jabatan yang sudah ada.',]);
        }
    }


    protected function validateRepeaterOverlap(array $items): void
    {
        $count = count($items);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $a = $items[$i];
                $b = $items[$j];

                if (
                    blank($a['jabatan_id'] ?? null) ||
                    blank($b['jabatan_id'] ?? null)
                ) {
                    continue;
                }

                /*
             * Hanya validasi jika jabatan + unitnya sama.
             */
                if (
                    ($a['jabatan_id'] ?? null) !== ($b['jabatan_id'] ?? null) ||
                    ($a['fakultas_id'] ?? null) !== ($b['fakultas_id'] ?? null) ||
                    ($a['prodi_id'] ?? null) !== ($b['prodi_id'] ?? null)
                ) {
                    continue;
                }

                $aMulai = $a['tanggal_mulai'] ?? null;
                $aSelesai = $a['tanggal_selesai'] ?? null;

                $bMulai = $b['tanggal_mulai'] ?? null;
                $bSelesai = $b['tanggal_selesai'] ?? null;

                if (
                    blank($aMulai) ||
                    blank($bMulai)
                ) {
                    continue;
                }

                /*
             * Cek overlap.
             */
                $aBerakhir = $aSelesai ?? '9999-12-31';
                $bBerakhir = $bSelesai ?? '9999-12-31';

                $overlap =
                    $aMulai <= $bBerakhir &&
                    $bMulai <= $aBerakhir;

                if ($overlap) {
                    throw ValidationException::withMessages([
                        "atribusiJabatan.{$j}.tanggal_mulai" =>
                        "Periode bertabrakan dengan riwayat jabatan pada item #" . ($i + 1) . '.',
                    ]);
                }
            }
        }
    }
}
