<?php

declare(strict_types=1);

namespace App\Imports\Kepegawaian;

use App\Enums\HR\JenisPegawai;
use App\Models\RefPerson;
use App\Models\TrxPegawai;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class PegawaiImporter extends Importer
{
    protected static ?string $model = TrxPegawai::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nik')
                ->label('NIK / No. KTP (SSOT)')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('nama_lengkap')
                ->label('Nama Lengkap')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('email')
                ->label('Email Pribadi')
                ->rules(['nullable', 'email', 'max:255']),

            ImportColumn::make('no_hp')
                ->label('No. Handphone')
                ->rules(['nullable', 'string', 'max:20']),

            ImportColumn::make('jenis_kelamin')
                ->label('Jenis Kelamin (L/P)')
                ->rules(['nullable', 'in:L,P,l,p']),

            ImportColumn::make('nip')
                ->label('NIP Pegawai')
                ->rules(['nullable', 'string', 'max:30']),

            ImportColumn::make('jenis_pegawai')
                ->label('Status Pegawai')
                ->requiredMapping()
                ->castStateUsing(function (string $state): ?string {
                    // Normalisasi teks dari Excel agar toleran terhadap typo/spasi
                    $normalized = strtoupper(trim(str_replace(['_', '-'], ' ', $state)));

                    return match ($normalized) {
                        'PNS' => JenisPegawai::PNS->value,
                        'PPPK' => JenisPegawai::PPPK->value,
                        'TETAP YAYASAN', 'TETAPYAYASAN' => JenisPegawai::TETAP_YAYASAN->value,
                        'KONTRAK' => JenisPegawai::KONTRAK->value,
                        'HONORER' => JenisPegawai::HONORER->value,
                        default => null,
                    };
                })
                ->rules(['required']),
        ];
    }

    /**
     * Override resolveRecord untuk integrasi SSOT.
     * Sistem akan mencari/membuat RefPerson lebih dulu, lalu menyambungkannya ke TrxPegawai.
     */
    public function resolveRecord(): ?TrxPegawai
    {
        $nik = $this->data['nik'];

        // 1. Cari atau buat entitas Profil Person (SSOT)
        $person = RefPerson::firstOrCreate(
            ['nik' => $nik],
            [
                'nama_lengkap'  => $this->data['nama_lengkap'],
                'email'         => $this->data['email'] ?? null,
                'no_hp'         => $this->data['no_hp'] ?? null,
                'jenis_kelamin' => isset($this->data['jenis_kelamin']) ? strtoupper($this->data['jenis_kelamin']) : null,
            ]
        );

        // 2. Resolve TrxPegawai berdasarkan NIP (jika ada), atau berdasarkan person_id
        if (!empty($this->data['nip'])) {
            return TrxPegawai::firstOrNew([
                'nip' => $this->data['nip'],
            ], [
                'person_id' => $person->id,
            ]);
        }

        return TrxPegawai::firstOrNew([
            'person_id' => $person->id,
        ]);
    }

    protected function beforeSave(): void
    {
        // Fallback untuk memastikan person_id terisi kuat sebelum masuk database
        if (! $this->record->person_id) {
            $person = RefPerson::where('nik', $this->data['nik'])->first();
            $this->record->person_id = $person?->id;
        }

        // Pastikan status pegawai baru di-set aktif secara default
        $this->record->is_active = $this->record->is_active ?? true;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import data pegawai telah selesai dan sukses memproses ' . number_format($import->successful_rows) . ' baris.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' Namun, terdapat ' . number_format($failedRowsCount) . ' baris yang gagal diimport (silakan unduh laporan CSV error untuk rinciannya).';
        }

        return $body;
    }
}
