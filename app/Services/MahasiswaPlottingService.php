<?php

namespace App\Services;

use App\Models\MahasiswaKelas;
use App\Models\Mahasiswa;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MahasiswaPlottingService
{
    /**
     * Plot mahasiswa ke kelas baru dengan validasi strict & komprehensif.
     */
    public function plot(string $mahasiswaId, string $kelasId, string|Carbon $tanggalMasuk): MahasiswaKelas
    {
        return DB::transaction(function () use ($mahasiswaId, $kelasId, $tanggalMasuk) {
            $mahasiswa = Mahasiswa::findOrFail($mahasiswaId);
            $kelas = Kelas::findOrFail($kelasId);
            if ($mahasiswa->angkatan_id !== $kelas->angkatan_id) {
                throw new \InvalidArgumentException("Mahasiswa angkatan {$mahasiswa->angkatan_id} tidak dapat dimasukkan ke kelas angkatan {$kelas->angkatan_id}.");
            }
            // 1. Validasi Prodi dan Program harus SAMA!
            if ($mahasiswa->prodi_id !== $kelas->prodi_id || $mahasiswa->program_id !== $kelas->program_id) {
                throw new \InvalidArgumentException('Prodi atau Program Kelas tidak sesuai dengan data Mahasiswa.');
            }

            // 2. Validasi Mahasiswa tidak aktif di kelas lain
            $hasActive = MahasiswaKelas::query()
                ->where('mahasiswa_id', $mahasiswaId)
                ->whereNull('tanggal_keluar')
                ->exists();

            if ($hasActive) {
                throw new \InvalidArgumentException('Mahasiswa tersebut masih terdaftar aktif di kelas lain.');
            }

            // 3. Validasi Kapasitas Kelas dengan Pessimistic Locking (lockForUpdate)
            // Mencegah Race Condition jika 2 admin nge-plot di detik yang sama
            $currentOccupancy = MahasiswaKelas::query()
                ->where('kelas_id', $kelasId)
                ->whereNull('tanggal_keluar')
                ->lockForUpdate()
                ->count();

            if ($currentOccupancy >= $kelas->kapasitas) {
                throw new \InvalidArgumentException("Kapasitas kelas penuh! (Maksimal: {$kelas->kapasitas} mahasiswa).");
            }

            return MahasiswaKelas::create([
                'mahasiswa_id' => $mahasiswaId,
                'kelas_id' => $kelasId,
                'tanggal_masuk' => Carbon::parse($tanggalMasuk)->toDateString(),
                'tanggal_keluar' => null,
            ]);
        });
    }

    /**
     * Mengeluarkan mahasiswa dari kelas tanpa menghapus data.
     */
    public function keluarDariKelas(MahasiswaKelas $mahasiswaKelas, string|Carbon $tanggalKeluar): void
    {
        if ($mahasiswaKelas->tanggal_keluar !== null) {
            throw new \InvalidArgumentException('Mahasiswa sudah berstatus nonaktif di kelas ini.');
        }

        $parsedTanggalKeluar = Carbon::parse($tanggalKeluar)->startOfDay();
        $parsedTanggalMasuk = Carbon::parse($mahasiswaKelas->tanggal_masuk)->startOfDay();

        // 4. Validasi Time Travel
        if ($parsedTanggalKeluar->lessThan($parsedTanggalMasuk)) {
            throw new \InvalidArgumentException('Tanggal keluar tidak boleh mendahului tanggal masuk (' . $mahasiswaKelas->tanggal_masuk . ').');
        }

        $mahasiswaKelas->update([
            'tanggal_keluar' => $parsedTanggalKeluar->toDateString(),
        ]);
    }

    /**
     * Pengecekan komprehensif histori akademik sebelum Hard Delete.
     */
    public function canDelete(MahasiswaKelas $mahasiswaKelas): bool
    {
        // 5. Cek seluruh tabel yang berelasi dengan eksistensi mahasiswa di kelas ini
        $hasKrs = DB::table('krs')
            ->where('mahasiswa_id', $mahasiswaKelas->mahasiswa_id)
            ->where('kelas_id', $mahasiswaKelas->kelas_id)
            ->exists();

        // Contoh tambahan (Sesuaikan dengan nama tabel di skema Anda)
        $hasTagihan = false;

        // Jika salah satu true, maka dilarang didelete
        return !($hasKrs || $hasTagihan);
    }

    public function forceDelete(MahasiswaKelas $mahasiswaKelas): void
    {
        if (!$this->canDelete($mahasiswaKelas)) {
            throw new \InvalidArgumentException('Data tidak dapat dihapus karena mahasiswa telah memiliki histori transaksi (KRS/Tagihan) di kelas ini.');
        }

        $mahasiswaKelas->delete();
    }
}
