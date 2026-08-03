<?php

namespace App\Services;

use App\Enums\PembimbingAkademikJenis;
use App\Enums\PembimbingAkademikStatus;
use App\Models\KonfigurasiPembimbingAkademik;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\PembimbingAkademik;
use App\Models\RefTahunAkademik;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PembimbingAkademikService
{
    /**
     * Ambil atau validasi konfigurasi pembimbing akademik berdasarkan prodi dan angkatan.
     *
     * @throws \DomainException
     */
    public function getKonfigurasiAktif(int $prodiId, int $angkatanId): KonfigurasiPembimbingAkademik
    {
        $config = KonfigurasiPembimbingAkademik::query()
            ->where('prodi_id', $prodiId)
            ->where('angkatan_id', $angkatanId)
            ->where('aktif', true)
            ->first();

        if (! $config) {
            throw new \DomainException('Konfigurasi Pembimbing Akademik untuk Program Studi dan Angkatan ini belum dikonfigurasi atau belum aktif.');
        }

        return $config;
    }

    /**
     * Memproses penugasan pembimbing tunggal (Manual Form Submission).
     */
    public function assignPembimbing(array $data): PembimbingAkademik
    {
        return DB::transaction(function () use ($data) {
            $config = $this->getKonfigurasiAktif((int) $data['prodi_id'], (int) $data['angkatan_id']);
            // Enforce Mode Konfigurasi
            if ($config->mode->name === 'PER_KELAS') {
                if (empty($data['kelas_id'])) {
                    throw new \InvalidArgumentException('Kelas wajib dipilih untuk penugasan mode PER_KELAS.');
                }
                $data['mahasiswa_id'] = null;
            } else {
                if (empty($data['mahasiswa_id'])) {
                    throw new \InvalidArgumentException('Mahasiswa wajib dipilih untuk penugasan mode PER_MAHASISWA.');
                }
                $data['kelas_id'] = null;
            }

            $this->validateConflict($data);

            return PembimbingAkademik::create([
                'kelas_id'          => $data['kelas_id'],
                'mahasiswa_id'      => $data['mahasiswa_id'],
                'dosen_id'          => $data['dosen_id'],
                'jenis'             => $data['jenis'],
                'is_primary'        => $data['is_primary'] ?? true,
                'semester_mulai_id' => $data['semester_mulai_id'],
                'tanggal_mulai'     => $data['tanggal_mulai'],
                'nomor_sk'          => $data['nomor_sk'] ?? null,
                'tanggal_sk'        => $data['tanggal_sk'] ?? null,
                'keterangan'        => $data['keterangan'] ?? null,
                'status'            => PembimbingAkademikStatus::AKTIF,
                'created_by'        => auth()->id(),
            ]);
        });
    }

    /**
     * Eksekusi Generate Penugasan Otomatis Massal
     */
    public function generateOtomatis(int $prodiId, int $angkatanId, int $dosenId, string $jenis, int $semesterMulaiId, string $tanggalMulai, ?string $nomorSk = null, ?string $tanggalSk = null): int
    {
        return DB::transaction(function () use ($prodiId, $angkatanId, $dosenId, $jenis, $semesterMulaiId, $tanggalMulai, $nomorSk, $tanggalSk) {
            $config = $this->getKonfigurasiAktif($prodiId, $angkatanId);
            $count = 0;

            if ($config->mode === 'PER_KELAS') {
                $kelaseList = Kelas::query()
                    ->where('prodi_id', $prodiId)
                    ->where('angkatan_id', $angkatanId)
                    ->get();

                foreach ($kelaseList as $kelas) {
                    $exists = PembimbingAkademik::query()
                        ->where('kelas_id', $kelas->id)
                        ->where('jenis', $jenis)
                        ->where('status', PembimbingAkademikStatus::AKTIF)
                        ->exists();

                    if (! $exists) {
                        PembimbingAkademik::create([
                            'kelas_id'          => $kelas->id,
                            'dosen_id'          => $dosenId,
                            'jenis'             => $jenis,
                            'is_primary'        => true,
                            'semester_mulai_id' => $semesterMulaiId,
                            'tanggal_mulai'     => $tanggalMulai,
                            'nomor_sk'          => $nomorSk,
                            'tanggal_sk'        => $tanggalSk,
                            'status'            => PembimbingAkademikStatus::AKTIF,
                            'created_by'        => auth()->id(),
                        ]);
                        $count++;
                    }
                }
            } else {
                $mahasiswaList = Mahasiswa::query()
                    ->where('prodi_id', $prodiId)
                    ->where('angkatan_id', $angkatanId)
                    ->get();

                foreach ($mahasiswaList as $mhs) {
                    $exists = PembimbingAkademik::query()
                        ->where('mahasiswa_id', $mhs->id)
                        ->where('jenis', $jenis)
                        ->where('status', PembimbingAkademikStatus::AKTIF)
                        ->exists();

                    if (! $exists) {
                        PembimbingAkademik::create([
                            'mahasiswa_id'      => $mhs->id,
                            'dosen_id'          => $dosenId,
                            'jenis'             => $jenis,
                            'is_primary'        => true,
                            'semester_mulai_id' => $semesterMulaiId,
                            'tanggal_mulai'     => $tanggalMulai,
                            'nomor_sk'          => $nomorSk,
                            'tanggal_sk'        => $tanggalSk,
                            'status'            => PembimbingAkademikStatus::AKTIF,
                            'created_by'        => auth()->id(),
                        ]);
                        $count++;
                    }
                }
            }

            return $count;
        });
    }

    /**
     * Melakukan mutasi (pergantian) pembimbing aktif secara atomic.
     */
    public function mutasiPembimbing(PembimbingAkademik $record, int $dosenBaruId, int $semesterSelesaiId, string $tanggalSelesai, ?string $alasan = null): PembimbingAkademik
    {
        return DB::transaction(function () use ($record, $dosenBaruId, $semesterSelesaiId, $tanggalSelesai, $alasan) {
            // 1. Nonaktifkan penugasan lama
            $record->update([
                'status'              => PembimbingAkademikStatus::SELESAI,
                'semester_selesai_id' => $semesterSelesaiId,
                'tanggal_selesai'     => $tanggalSelesai,
                'alasan'              => $alasan,
                'updated_by'          => auth()->id(),
            ]);

            // 2. Buat penugasan baru
            return PembimbingAkademik::create([
                'kelas_id'          => $record->kelas_id,
                'mahasiswa_id'      => $record->mahasiswa_id,
                'dosen_id'          => $dosenBaruId,
                'jenis'             => $record->jenis,
                'is_primary'        => $record->is_primary,
                'semester_mulai_id' => $semesterSelesaiId,
                'tanggal_mulai'     => $tanggalSelesai,
                'nomor_sk'          => $record->nomor_sk,
                'tanggal_sk'        => $record->tanggal_sk,
                'status'            => PembimbingAkademikStatus::AKTIF,
                'created_by'        => auth()->id(),
            ]);
        });
    }

    /**
     * Validasi tumpang tindih / eksistensi pembimbing aktif.
     */
    protected function validateConflict(array $data): void
    {
        $query = PembimbingAkademik::query()
            ->where('jenis', $data['jenis'])
            ->where('status', PembimbingAkademikStatus::AKTIF);

        if (! empty($data['kelas_id'])) {
            $query->where('kelas_id', $data['kelas_id']);
        } else {
            $query->where('mahasiswa_id', $data['mahasiswa_id']);
        }

        if ($query->exists()) {
            throw new \DomainException('Target penugasan ini sudah memiliki pembimbing aktif dengan jenis yang sama.');
        }
    }
}
