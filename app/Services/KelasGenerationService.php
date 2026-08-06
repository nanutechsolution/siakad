<?php

namespace App\Services;

use App\Models\Kelas;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Semua aturan "berapa kelas, berapa kapasitas per kelas" hidup di sini —
 * bukan di GenerateKelasWizard — supaya Page tetap murni UI/orkestrasi,
 * konsisten dengan MahasiswaPlottingService & MahasiswaMutasiService.
 */
class KelasGenerationService
{
    /**
     * Menghitung preview kelas yang AKAN dibuat, tanpa menyentuh database.
     * Operator mengisi salah satu dari $jumlahKelas atau $kapasitasMaksimal;
     * yang satunya lagi diturunkan otomatis dari jumlah mahasiswa belum berkelas.
     *
     * @return Collection<int, array{nama_kelas: string, kapasitas: int}>
     */
    public function hitungPreview(
        int $jumlahMahasiswaBelumKelas,
        ?int $jumlahKelas,
        ?int $kapasitasMaksimal,
        string $prefixNamaKelas
    ): Collection {
        if ($jumlahMahasiswaBelumKelas <= 0) {
            return collect();
        }

        if ($kapasitasMaksimal) {
            $jumlahKelasFinal = (int) ceil($jumlahMahasiswaBelumKelas / $kapasitasMaksimal);
            $kapasitasPerKelas = $kapasitasMaksimal;
        } else {
            $jumlahKelasFinal = max(1, $jumlahKelas ?? 1);
            $kapasitasPerKelas = (int) ceil($jumlahMahasiswaBelumKelas / $jumlahKelasFinal);
        }

        return collect(range(0, $jumlahKelasFinal - 1))
            ->map(fn(int $index) => [
                'nama_kelas' => $prefixNamaKelas . '-' . $this->hurufKe($index),
                'kapasitas' => $kapasitasPerKelas,
            ]);
    }

    /**
     * Membuat baris `kelas` KOSONG (tanpa plotting mahasiswa) dari hasil preview.
     * Plotting tetap dilakukan terpisah lewat PlottingMahasiswaPage — memisahkan
     * ini mencegah satu kesalahan hitung merusak data plotting yang sudah ada.
     *
     * @param Collection<int, array{nama_kelas: string, kapasitas: int}> $preview
     * @return Collection<int, Kelas>
     */
    public function simpan(Collection $preview, int $prodiId, int $programId, int $angkatanId): Collection
    {
        return DB::transaction(function () use ($preview, $prodiId, $programId, $angkatanId) {
            return $preview->map(fn(array $item) => Kelas::create([
                'nama_kelas' => $item['nama_kelas'],
                'prodi_id' => $prodiId,
                'program_id' => $programId,
                'angkatan_id' => $angkatanId,
                'kapasitas' => $item['kapasitas'],
            ]));
        });
    }

    /**
     * 0 -> A, 1 -> B, ..., 25 -> Z, 26 -> AA, dst. — supaya generate tidak
     * mentok kalau jumlah kelas lebih dari 26.
     */
    protected function hurufKe(int $index): string
    {
        $huruf = '';
        $index++;

        while ($index > 0) {
            $sisa = ($index - 1) % 26;
            $huruf = chr(65 + $sisa) . $huruf;
            $index = (int) (($index - $sisa) / 26);
        }

        return $huruf;
    }
}
