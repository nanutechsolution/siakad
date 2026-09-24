<?php

declare(strict_types=1);

namespace App\Services\Akademik;

use App\Models\Mahasiswa;
use App\Models\RefProdi;
use App\Settings\KampusSettings;

final class NimService
{
    public const DEFAULT_FORMAT = '{THN}{KODE}{NO:3}';

    public function __construct(
        private readonly KampusSettings $kampusSettings,
    ) {}

    /**
     * Alokasikan NIM baru untuk mahasiswa pada Prodi tujuan.
     *
     * Harus dipanggil di dalam transaksi: baris RefProdi dikunci (lockForUpdate)
     * supaya sequence tidak bentrok antar proses yang berjalan bersamaan.
     * Method ini hanya menghitung; pembaruan mahasiswa & counter tetap
     * urusan caller agar sebagian perubahan tidak mungkin tersimpan sebagian.
     */
    public function generate(Mahasiswa $mahasiswa, RefProdi $prodi): string
    {
        $prodi = RefProdi::query()->lockForUpdate()->findOrFail($prodi->getKey());

        $sequence = $this->nextSequence($prodi, $mahasiswa, true);
        $nim = $this->build($mahasiswa, $prodi, $sequence);

        // Tabrakan NIM antar mahasiswa (mis. data lama hasil migrasi) -> naikkan sequence.
        while (Mahasiswa::query()->where('nim', $nim)->whereKeyNot($mahasiswa->getKey())->exists()) {
            $sequence++;
            $nim = $this->build($mahasiswa, $prodi, $sequence);
        }

        $prodi->update(['last_nim_seq' => $sequence]);

        return $nim;
    }

    /**
     * NIM yang akan dipakai bila mutasi disimpan sekarang.
     * Hanya perkiraan: tanpa kunci, bisa sedikit berbeda dari hasil generate.
     */
    public function preview(Mahasiswa $mahasiswa, ?RefProdi $prodi): ?string
    {
        if (! $prodi) {
            return null;
        }

        return $this->build($mahasiswa, $prodi, $this->nextSequence($prodi, $mahasiswa, false));
    }

    public function render(string $format, int $tahun, string $kodeProdi, int $sequence): string
    {
        $nim = str_replace(
            ['{TAHUN}', '{THN}', '{KODE}'],
            [(string) $tahun, substr((string) $tahun, -2), $kodeProdi],
            $format,
        );

        if (preg_match('/\{NO:(\d+)\}/', $nim, $matches)) {
            $digits = max(1, (int) $matches[1]);

            return str_replace($matches[0], str_pad((string) $sequence, $digits, '0', STR_PAD_LEFT), $nim);
        }

        return str_replace('{NO}', str_pad((string) $sequence, 3, '0', STR_PAD_LEFT), $nim);
    }

    private function build(Mahasiswa $mahasiswa, RefProdi $prodi, int $sequence): string
    {
        return $this->render(
            $prodi->format_nim ?? self::DEFAULT_FORMAT,
            (int) $mahasiswa->angkatan_id,
            (string) $prodi->kode_prodi_internal,
            $sequence,
        );
    }

    private function nextSequence(RefProdi $prodi, Mahasiswa $mahasiswa, bool $lock): int
    {
        // Reset per tahun: sequence dihitung ulang dari NIM tertinggi
        // mahasiswa prodi & angkatan yang sama (tanpa prefix PMB).
        if ($this->kampusSettings->reset_nim_tahunan) {
            $query = Mahasiswa::query()
                ->where('prodi_id', $prodi->getKey())
                ->where('angkatan_id', $mahasiswa->angkatan_id)
                ->where('nim', 'NOT LIKE', 'PMB%')
                ->orderByDesc('nim');

            $last = ($lock ? $query->lockForUpdate() : $query)->first();

            return $last ? ((int) substr((string) $last->nim, -3)) + 1 : 1;
        }

        return ((int) $prodi->last_nim_seq) + 1;
    }
}
