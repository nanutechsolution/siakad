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

    /**
     * Validasi satu item riwayat jabatan.
     *
     * Repeater Filament dapat menggunakan key string/UUID,
     * sehingga $index tidak boleh dipaksa menjadi int.
     */
    protected function validateJabatanItem(
        array $item,
        string|int $index,
        mixed $currentRecordId = null,
    ): void {
        $jabatanId = $item['jabatan_id'] ?? null;
        $fakultasId = $item['fakultas_id'] ?? null;
        $prodiId = $item['prodi_id'] ?? null;
        $tanggalMulai = $item['tanggal_mulai'] ?? null;
        $tanggalSelesai = $item['tanggal_selesai'] ?? null;

        if (blank($jabatanId) || blank($tanggalMulai)) {
            return;
        }

        /*
         * Validasi tanggal.
         */
        if (
            filled($tanggalSelesai) &&
            $tanggalSelesai < $tanggalMulai
        ) {
            throw ValidationException::withMessages([
                "atribusiJabatan.{$index}.tanggal_selesai" =>
                'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
            ]);
        }

        /*
         * Person berasal dari record TrxDosen.
         */
        $personId = $this->record?->person_id
            ?? $this->data['person_id']
            ?? null;

        if (blank($personId)) {
            return;
        }

        /*
         * Cari riwayat jabatan milik person yang sama,
         * dengan jabatan + fakultas + prodi yang sama.
         */
        $query = TrxPersonJabatan::query()
            ->where('person_id', $personId)
            ->where('jabatan_id', $jabatanId)
            ->where(function ($query) use ($fakultasId) {
                if (is_null($fakultasId)) {
                    $query->whereNull('fakultas_id');
                } else {
                    $query->where('fakultas_id', $fakultasId);
                }
            })
            ->where(function ($query) use ($prodiId) {
                if (is_null($prodiId)) {
                    $query->whereNull('prodi_id');
                } else {
                    $query->where('prodi_id', $prodiId);
                }
            });

        /*
         * Jangan membandingkan record dengan dirinya sendiri.
         *
         * Catatan:
         * Untuk Repeater relationship, ID child record bisa tersedia
         * di state sebagai "id".
         */
        $childRecordId = $item['id'] ?? null;

        if (filled($childRecordId)) {
            $query->where('id', '!=', $childRecordId);
        }

        /*
         * Jika currentRecordId diberikan, tetap aman untuk
         * mencegah pengecualian parent record bila diperlukan.
         */
        if (filled($currentRecordId)) {
            $query->where(function ($query) use ($currentRecordId) {
                $query->whereNull('id')
                    ->orWhere('id', '!=', $currentRecordId);
            });
        }

        /*
         * Logika overlap:

         * Existing mulai <= New selesai
         *
         * DAN
         *
         * Existing selesai >= New mulai
         *
         * NULL pada tanggal selesai = masih aktif / tidak terbatas.
         */
        $query
            ->where(
                'tanggal_mulai',
                '<=',
                $tanggalSelesai ?? '9999-12-31'
            )
            ->where(function ($query) use ($tanggalMulai) {
                $query
                    ->whereNull('tanggal_selesai')
                    ->orWhere('tanggal_selesai', '>=', $tanggalMulai);
            });

        if ($query->exists()) {
            throw ValidationException::withMessages([
                "atribusiJabatan.{$index}.tanggal_mulai" =>
                'Periode jabatan bertabrakan dengan riwayat jabatan yang sudah ada.',
            ]);
        }
    }

    /**
     * Validasi overlap antar item yang sedang ada di Repeater.
     *
     * Jangan mengandalkan key Repeater berupa 0,1,2...
     * karena Filament dapat menggunakan UUID/string.
     */
    protected function validateRepeaterOverlap(array $items): void
    {
        /*
         * Normalisasi menjadi array numerik hanya untuk
         * kebutuhan perbandingan internal.
         *
         * Key asli tetap digunakan untuk pesan validation.
         */
        $normalized = [];

        foreach ($items as $key => $item) {
            $normalized[] = [
                'key' => $key,
                'item' => $item,
            ];
        }

        $count = count($normalized);

        for ($i = 0; $i < $count; $i++) {
            $a = $normalized[$i]['item'];
            $aKey = $normalized[$i]['key'];

            if (blank($a['jabatan_id'] ?? null)) {
                continue;
            }

            $aMulai = $a['tanggal_mulai'] ?? null;
            $aSelesai = $a['tanggal_selesai'] ?? null;

            if (blank($aMulai)) {
                continue;
            }

            for ($j = $i + 1; $j < $count; $j++) {
                $b = $normalized[$j]['item'];
                $bKey = $normalized[$j]['key'];

                if (blank($b['jabatan_id'] ?? null)) {
                    continue;
                }

                /*
                 * Hanya dibandingkan jika jabatan + unit sama.
                 */
                if (
                    ($a['jabatan_id'] ?? null) !== ($b['jabatan_id'] ?? null) ||
                    ($a['fakultas_id'] ?? null) !== ($b['fakultas_id'] ?? null) ||
                    ($a['prodi_id'] ?? null) !== ($b['prodi_id'] ?? null)
                ) {
                    continue;
                }

                $bMulai = $b['tanggal_mulai'] ?? null;
                $bSelesai = $b['tanggal_selesai'] ?? null;

                if (blank($bMulai)) {
                    continue;
                }

                /*
                 * NULL tanggal selesai berarti masih aktif.
                 */
                $aBerakhir = $aSelesai ?? '9999-12-31';
                $bBerakhir = $bSelesai ?? '9999-12-31';

                /*
                 * Cek overlap periode.
                 */
                $overlap =
                    $aMulai <= $bBerakhir &&
                    $bMulai <= $aBerakhir;

                if ($overlap) {
                    throw ValidationException::withMessages([
                        "atribusiJabatan.{$bKey}.tanggal_mulai" =>
                        'Periode bertabrakan dengan riwayat jabatan lainnya.',
                    ]);
                }
            }
        }
    }
}
