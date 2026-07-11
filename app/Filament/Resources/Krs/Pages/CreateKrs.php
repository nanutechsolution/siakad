<?php

declare(strict_types=1);

namespace App\Filament\Resources\Krs\Pages;

use App\Filament\Resources\Krs\KrsResource;
use App\Models\JadwalKuliah;
use App\Models\KrsDetail;
use App\Models\Mahasiswa;
use App\Models\RefTahunAkademik;
use App\Services\Akademik\KrsValidationService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateKrs extends CreateRecord
{
    protected static string $resource = KrsResource::class;
    protected static bool $canCreateAnother = false;

    /**
     * Memastikan validasi di backend tidak bisa ditembus 
     * meski UI checkbox diakali oleh Admin/Pengguna.
     */
    protected function beforeCreate(): void
    {
        $data = $this->form->getState();
        $mahasiswa = Mahasiswa::find($data['mahasiswa_id'] ?? null);
        $ta = RefTahunAkademik::find($data['tahun_akademik_id'] ?? null);
        $jadwalIds = $data['jadwal_kuliah_ids'] ?? [];

        if ($mahasiswa && $ta) {
            $service = app(KrsValidationService::class);

            // 1. Gate Kontinuitas (Gap Semester)
            $valKontinuitas = $service->checkStatusMahasiswa($mahasiswa, $ta);
            if (!$valKontinuitas->passed) {
                Notification::make()->danger()->title('Validasi Akademik Gagal')->body($valKontinuitas->message)->send();
                $this->halt();
            }

            // 2. Gate Keuangan (Payment Policy)
            $valKeuangan = $service->checkKeuangan($mahasiswa, $ta, false);
            if (!$valKeuangan->passed) {
                Notification::make()->danger()->title('Blokir Keuangan')->body($valKeuangan->message)->send();
                $this->halt();
            }

            // Hitung total SKS yang dicentang admin untuk validasi selanjutnya
            $totalSksDiambil = 0;
            if (!empty($jadwalIds)) {
                $totalSksDiambil = (int) DB::table('jadwal_kuliah')
                    ->join('master_mata_kuliahs', 'master_mata_kuliahs.id', '=', 'jadwal_kuliah.mata_kuliah_id')
                    ->whereIn('jadwal_kuliah.id', $jadwalIds)
                    ->sum('master_mata_kuliahs.sks_default');
            }

            // 3. Gate SKS Maksimal
            // Cek apakah mahasiswa punya dispensasi aktif untuk bypass limit SKS
            $hasDispensasiSks = DB::table('dispensasi_akademiks')
                ->where('mahasiswa_id', $mahasiswa->id)
                ->where('jenis', 'KRS')
                ->where('status', 'AKTIF')
                ->where('berlaku_mulai', '<=', $ta->tgl_selesai_krs)
                ->where('berlaku_sampai', '>=', $ta->tgl_mulai_krs)
                ->exists();

            $valSks = $service->checkSksMaksimal($mahasiswa, $totalSksDiambil, $hasDispensasiSks);
            if (!$valSks->passed) {
                Notification::make()->danger()->title('Batas SKS Terlampaui')->body($valSks->message)->send();
                $this->halt();
            }

            // 4. Gate Duplikasi & Bentrok Jadwal
            $valJadwal = $service->checkDuplikasiDanBentrok($jadwalIds);
            if (!$valJadwal->passed) {
                Notification::make()->danger()->title('Jadwal Bentrok')->body($valJadwal->message)->send();
                $this->halt();
            }

            // 5. Gate Kuota Kelas
            $valKuota = $service->checkKuotaKelas($jadwalIds);
            if (!$valKuota->passed) {
                Notification::make()->danger()->title('Kapasitas Kelas Penuh')->body($valKuota->message)->send();
                $this->halt();
            }
        }
    }

    protected function handleRecordCreation(array $data): Model
    {
        $mahasiswa = Mahasiswa::find($data['mahasiswa_id']);

        return DB::transaction(function () use ($data, $mahasiswa) {
            // 1. Buat Header KRS
            $krs = static::getModel()::create([
                'mahasiswa_id' => $data['mahasiswa_id'],
                'tahun_akademik_id' => $data['tahun_akademik_id'],
                'status_krs' => $data['status_krs'],
                'dosen_wali_id' => $mahasiswa->dosen_wali_id ?? null,
                'is_paket_snapshot' => $mahasiswa->prodi->is_paket ?? 1,
            ]);

            // 2. Insert KrsDetail (Observer akan menghitung ulang isi_kelas dan total_sks)
            if (!empty($data['jadwal_kuliah_ids'])) {
                foreach ($data['jadwal_kuliah_ids'] as $jadwalId) {
                    $jadwal = JadwalKuliah::with('mataKuliah')->find($jadwalId);

                    if ($jadwal && $jadwal->mataKuliah) {
                        KrsDetail::create([
                            'krs_id' => $krs->id,
                            'jadwal_kuliah_id' => $jadwal->id,
                            'mata_kuliah_id' => $jadwal->mata_kuliah_id,
                            'kode_mk_snapshot' => $jadwal->mataKuliah->kode_mk,
                            'nama_mk_snapshot' => $jadwal->mataKuliah->nama_mk,
                            'sks_snapshot' => $jadwal->mataKuliah->sks_default,
                            'status_ambil' => 'B',
                        ]);
                    }
                }
            }

            return $krs;
        });
    }
}
