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
                ->label('NIK')
                ->requiredMapping()
                ->castStateUsing(fn ($state) => self::normalizeNik($state))
                ->rules([
                    'required',
                    'digits:16',
                ])
                ->validationMessages([
                    'digits' => 'NIK harus 16 digit angka. Kemungkinan kolom NIK di file sumber ' .
                        'berformat Angka (bukan Teks) sehingga digit nol di depan hilang / angka ' .
                        'terbulatkan. Format ulang kolom NIK di Excel sebagai Text sebelum export.',
                    'required' => 'NIK wajib diisi.',
                ]),


            ImportColumn::make('nama_lengkap')
                ->label('Nama Lengkap')
                ->requiredMapping()
                ->castStateUsing(fn ($state) => $state === null ? null : trim((string) $state))
                ->rules([
                    'required',
                    'max:255',
                ]),


            ImportColumn::make('email')
                ->label('Email')
                ->castStateUsing(function ($state) {
                    $state = $state === null ? null : trim((string) $state);

                    return $state === '' ? null : $state;
                })
                ->rules([
                    'nullable',
                    'email',
                ]),


            ImportColumn::make('no_hp')
                ->label('No HP')
                ->castStateUsing(function ($state) {
                    if ($state === null) {
                        return null;
                    }

                    // buang karakter non digit kecuali leading '+'
                    $state = trim((string) $state);
                    $state = preg_replace('/[^0-9+]/', '', $state);

                    return $state === '' ? null : $state;
                })
                ->rules([
                    'nullable',
                ]),


            ImportColumn::make('jenis_kelamin')
                ->label('Jenis Kelamin')
                ->castStateUsing(fn ($state) => self::normalizeJenisKelamin($state))
                ->rules([
                    'nullable',
                    'in:L,P',
                ])
                ->validationMessages([
                    'in' => 'Jenis Kelamin harus L atau P (menerima juga "Laki-laki"/"Perempuan").',
                ]),


            ImportColumn::make('nip')
                ->label('NIP')
                ->castStateUsing(function ($state) {
                    if ($state === null) {
                        return null;
                    }

                    $state = trim((string) $state);

                    return $state === '' ? null : $state;
                })
                ->rules([
                    'nullable',
                ]),


            ImportColumn::make('jenis_pegawai')
                ->label('Jenis Pegawai')
                ->requiredMapping()
                ->castStateUsing(fn ($state) => self::normalizeJenisPegawai($state))
                ->rules([
                    'required',
                ])
                ->validationMessages([
                    'required' => 'Jenis Pegawai tidak dikenali. Nilai valid: PNS, PPPK, ' .
                        'TETAP YAYASAN, KONTRAK, HONORER (variasi penulisan umum juga diterima).',
                ]),

        ];
    }


    /**
     * NIK: buang semua karakter non-digit, dan tangani kasus Excel
     * yang mengekspor angka besar dalam notasi scientific (mis. 3.21001E+15)
     * atau dengan pemisah ribuan.
     */
    protected static function normalizeNik(mixed $state): ?string
    {
        if ($state === null) {
            return null;
        }

        $raw = trim((string) $state);

        if ($raw === '') {
            return null;
        }

        // Notasi scientific dari Excel, mis: 3.21001E+15
        if (preg_match('/^\d+(\.\d+)?E\+\d+$/i', $raw)) {
            $raw = number_format((float) $raw, 0, '', '');
        }

        return preg_replace('/[^0-9]/', '', $raw);
    }


    protected static function normalizeJenisKelamin(mixed $state): ?string
    {
        if ($state === null) {
            return null;
        }

        $state = strtoupper(trim((string) $state));

        if ($state === '') {
            return null;
        }

        return match (true) {
            in_array($state, ['L', 'LAKI-LAKI', 'LAKI LAKI', 'PRIA', 'M', 'MALE'], true) => 'L',
            in_array($state, ['P', 'PEREMPUAN', 'WANITA', 'F', 'FEMALE'], true) => 'P',
            default => $state, // biarkan lolos ke rule 'in:L,P' agar tervalidasi & dilaporkan gagal
        };
    }


    /**
     * Normalisasi toleran terhadap variasi spasi/penulisan umum dari operator input Excel.
     * Mengembalikan null HANYA jika benar-benar tidak ada padanan -> supaya pesan error
     * 'required' custom di atas benar-benar berarti "tidak dikenali", bukan "kosong".
     */
    protected static function normalizeJenisPegawai(mixed $state): ?string
    {
        if ($state === null) {
            return null;
        }

        $raw = strtoupper(trim((string) $state));
        $raw = preg_replace('/\s+/', ' ', $raw); // rapikan spasi ganda

        if ($raw === '') {
            return null;
        }

        return match (true) {
            in_array($raw, ['PNS'], true)
                => JenisPegawai::PNS->value,

            in_array($raw, ['PPPK', 'P3K'], true)
                => JenisPegawai::PPPK->value,

            in_array($raw, ['TETAP YAYASAN', 'DOSEN TETAP YAYASAN', 'PEGAWAI TETAP YAYASAN', 'YAYASAN'], true)
                => JenisPegawai::TETAP_YAYASAN->value,

            in_array($raw, ['KONTRAK', 'PEGAWAI KONTRAK'], true)
                => JenisPegawai::KONTRAK->value,

            in_array($raw, ['HONORER', 'HONOR'], true)
                => JenisPegawai::HONORER->value,

            default => null,
        };
    }


    public function resolveRecord(): ?TrxPegawai
    {
        // Pada titik ini $this->data['nik'] SUDAH melalui castStateUsing (sudah bersih),
        // jadi tidak perlu preg_replace ulang.
        $nik = $this->data['nik'];

        /**
         * SSOT PERSON
         */
        $person = RefPerson::updateOrCreate(
            [
                'nik' => $nik,
            ],
            [
                'nama_lengkap' => $this->data['nama_lengkap'],
                'email' => $this->data['email'] ?? null,
                'no_hp' => $this->data['no_hp'] ?? null,
                'jenis_kelamin' => $this->data['jenis_kelamin'] ?? null,
            ]
        );


        /**
         * PEGAWAI
         */
        return TrxPegawai::withTrashed()
            ->firstOrNew([
                'person_id' => $person->id,
            ]);
    }


    public function fillRecord(): void
    {
        $this->record->fill([
            'person_id' => $this->record->person_id,
            'nip' => $this->data['nip'] ?? null,
            'jenis_pegawai' => $this->data['jenis_pegawai'],
            'is_active' => true,
        ]);

        if ($this->record->trashed()) {
            $this->record->restore();
        }
    }


    protected function beforeSave(): void
    {
        logger()->info(
            'IMPORT PEGAWAI',
            [
                'data' => $this->data,
                'pegawai' => $this->record->toArray(),
            ]
        );
    }


    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import pegawai selesai: ' . number_format($import->successful_rows) . ' berhasil.';

        $failedRowsCount = $import->getFailedRowsCount();

        if ($failedRowsCount > 0) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal — unduh log gagal untuk detail penyebabnya per baris.';
        }

        return $body;
    }
}