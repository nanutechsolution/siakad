<?php

namespace App\Services;

use App\Enums\PembimbingAkademikJenis;
use App\Models\Mahasiswa;
use App\Models\TrxDosen;
use App\Support\Utf8;
use Illuminate\Support\Collection;

/**
 * Mengubah baris mentah hasil parsing Excel menjadi baris yang sudah
 * divalidasi + diperkaya (nama mahasiswa/dosen) untuk ditampilkan di
 * layar Preview sebelum benar-benar disimpan ke database.
 *
 * Sengaja TIDAK mengembalikan instance Model (hanya id + label string)
 * supaya hasilnya aman disimpan di public property Livewire array.
 *
 * PENTING: semua string disaring lewat Utf8::clean() sebelum masuk ke
 * array hasil — lihat App\Support\Utf8 untuk alasannya (mencegah
 * "Malformed UTF-8 characters" saat Livewire serialize state).
 */
class PenugasanImportParser
{
    /**
     * @param  array<int, array<string, mixed>>  $rows  baris data (tanpa heading row), key kolom sudah lowercase
     * @return Collection<int, array<string, mixed>>
     */
    public function parse(array $rows): Collection
    {
        return collect($rows)
            ->map(function (array $row, int $index) {
                $errors = [];

                $nim = Utf8::clean(trim((string) ($row['nim_mahasiswa'] ?? '')));
                $nidn = Utf8::clean(trim((string) ($row['nidn_dosen'] ?? '')));
                $jenisRaw = strtoupper(Utf8::clean(trim((string) ($row['jenis'] ?? 'DOSEN_WALI'))));
                $tanggalMulai = Utf8::clean(trim((string) ($row['tanggal_mulai'] ?? '')));
                $keterangan = Utf8::clean(trim((string) ($row['keterangan'] ?? '')));

                if ($nim === '') {
                    $errors[] = 'NIM kosong';
                }

                if ($nidn === '') {
                    $errors[] = 'NIDN kosong';
                }

                $jenisValue = null;
                $jenisValid = collect(PembimbingAkademikJenis::cases())->contains(fn($case) => $case->value === $jenisRaw);

                if (! $jenisValid) {
                    $errors[] = "Jenis '{$jenisRaw}' tidak dikenali";
                } else {
                    $jenisValue = $jenisRaw;
                }

                $mahasiswa = $nim !== '' ? Mahasiswa::query()->where('nim', $nim)->first() : null;

                if ($nim !== '' && ! $mahasiswa) {
                    $errors[] = "Mahasiswa NIM {$nim} tidak ditemukan";
                }

                $dosen = $nidn !== '' ? TrxDosen::query()->where('nidn', $nidn)->first() : null;

                if ($nidn !== '' && ! $dosen) {
                    $errors[] = "Dosen NIDN {$nidn} tidak ditemukan";
                }

                if ($tanggalMulai === '') {
                    $tanggalMulai = now()->toDateString();
                }

                return [
                    'baris' => $index + 2, // +2: baris 1 = heading, index mulai dari 0
                    'nim' => $nim,
                    'nidn' => $nidn,
                    'jenis' => $jenisValue,
                    'mahasiswa_id' => $mahasiswa?->id,
                    // Nama dari DB juga disaring — jaga-jaga kalau ada data lama
                    // yang ternyata sudah tersimpan dengan encoding bukan UTF-8.
                    'mahasiswa_nama' => Utf8::clean($mahasiswa?->person?->nama_lengkap),
                    'dosen_id' => $dosen?->id,
                    'dosen_nama' => Utf8::clean($dosen?->person?->nama_lengkap),
                    'tanggal_mulai' => $tanggalMulai,
                    'keterangan' => $keterangan !== '' ? $keterangan : null,
                    'errors' => array_map(fn(string $e) => Utf8::clean($e), $errors),
                    'valid' => $errors === [],
                ];
            })
            ->values();
    }
}
