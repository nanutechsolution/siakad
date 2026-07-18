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
use Illuminate\Support\Str;

class CreateKrs extends CreateRecord
{
    protected static string $resource = KrsResource::class;
    protected static bool $canCreateAnother = false;

    protected function beforeCreate(): void
    {
        $data = $this->form->getState();
        $mahasiswa = Mahasiswa::find($data['mahasiswa_id'] ?? null);
        $ta = RefTahunAkademik::find($data['tahun_akademik_id'] ?? null);

        // PERBAIKAN: Ambil dan gabungkan kedua jenis jadwal untuk divalidasi
        $jadwalUtama = $data['jadwal_kuliah_ids'] ?? [];
        $jadwalMengulang = $data['jadwal_mengulang_ids'] ?? [];
        $jadwalIds = array_unique(array_merge($jadwalUtama, $jadwalMengulang));

        // Validasi jika admin tidak menceklis apa-apa sama sekali
        if (empty($jadwalIds)) {
            Notification::make()->warning()->title('Peringatan')->body('Pilih minimal satu kelas/jadwal.')->send();
            $this->halt();
        }

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

            // Hitung total SKS (Semua) dan SKS Mengulang
            $totalSksDiambil = (int) DB::table('jadwal_kuliah')
                ->join('master_mata_kuliahs', 'master_mata_kuliahs.id', '=', 'jadwal_kuliah.mata_kuliah_id')
                ->whereIn('jadwal_kuliah.id', $jadwalIds)
                ->sum('master_mata_kuliahs.sks_default');

            $totalSksMengulang = (int) DB::table('jadwal_kuliah')
                ->join('master_mata_kuliahs', 'master_mata_kuliahs.id', '=', 'jadwal_kuliah.mata_kuliah_id')
                ->whereIn('jadwal_kuliah.id', $jadwalMengulang)
                ->sum('master_mata_kuliahs.sks_default');

            // 3. Gate SKS Maksimal
            $hasDispensasiSks = DB::table('dispensasi_akademiks')
                ->where('mahasiswa_id', $mahasiswa->id)
                ->where('jenis', 'KRS')
                ->where('status', 'AKTIF')
                ->where('berlaku_mulai', '<=', $ta->tgl_selesai_krs)
                ->where('berlaku_sampai', '>=', $ta->tgl_mulai_krs)
                ->exists();

            // Sesuai dengan parameter service Anda di halaman mahasiswa
            $valSks = $service->checkSksMaksimal($mahasiswa, $totalSksDiambil, $hasDispensasiSks, $totalSksMengulang);
            if (!$valSks->passed) {
                Notification::make()->danger()->title('Batas SKS Terlampaui')->body($valSks->message)->send();
                $this->halt();
            }

            // 4. Gate Duplikasi & Bentrok Jadwal (Pakai $jadwalIds gabungan)
            $valJadwal = $service->checkDuplikasiDanBentrok($jadwalIds);
            if (!$valJadwal->passed) {
                Notification::make()->danger()->title('Jadwal Bentrok')->body($valJadwal->message)->send();
                $this->halt();
            }

            // 5. Gate Kuota Kelas (Pakai $jadwalIds gabungan)
            $valKuota = $service->checkKuotaKelas($jadwalIds);
            if (!$valKuota->passed) {
                Notification::make()->danger()->title('Kapasitas Kelas Penuh')->body($valKuota->message)->send();
                $this->halt();
            }
        }
    }

    protected function handleRecordCreation(array $data): Model
    {
        // 1. Pisahkan array jadwal dari data utama
        $jadwalUtama = $data['jadwal_kuliah_ids'] ?? [];
        $jadwalMengulang = $data['jadwal_mengulang_ids'] ?? [];

        // Hapus array tersebut agar tidak ikut di-insert ke tabel KRS
        unset($data['jadwal_kuliah_ids'], $data['jadwal_mengulang_ids'], $data['is_eligible'], $data['validation_msg'], $data['active_kelas_id'], $data['mode_krs'], $data['prodi_id']);

        // Kembalikan hasil dari DB::transaction langsung ke variabel $record
        $record = DB::transaction(function () use ($data, $jadwalUtama, $jadwalMengulang) {
            // Gabungkan ID jadwal tanpa duplikat
            $jadwalIds = array_unique(array_merge($jadwalUtama, $jadwalMengulang));

            // Hitung total SKS
            $totalSksDiambil = (int) DB::table('jadwal_kuliah')
                ->join('master_mata_kuliahs', 'master_mata_kuliahs.id', '=', 'jadwal_kuliah.mata_kuliah_id')
                ->whereIn('jadwal_kuliah.id', $jadwalIds)
                ->sum('master_mata_kuliahs.sks_default');

            // Buat ID KRS
            $data['id'] = Str::uuid()->toString();
            $data['total_sks_diambil'] = $totalSksDiambil;
            $data['diajukan_at'] = now();

            // 2. Insert Header KRS menggunakan metode model Filament
            $createdRecord = static::getModel()::create($data);

            // 3. Insert Details KRS
            $detailInserts = [];
            foreach ($jadwalIds as $jId) {
                $mataKuliahId = DB::table('jadwal_kuliah')->where('id', $jId)->value('mata_kuliah_id');
                $sks = DB::table('master_mata_kuliahs')->where('id', $mataKuliahId)->value('sks_default');
                $statusAmbil = in_array($jId, $jadwalMengulang, true) ? 'U' : 'B';

                $detailInserts[] = [
                    'krs_id'           => $createdRecord->id,
                    'jadwal_kuliah_id' => $jId,
                    'mata_kuliah_id'   => $mataKuliahId,
                    'sks_snapshot'     => $sks,
                    'status_ambil'     => $statusAmbil,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }

            if (!empty($detailInserts)) {
                DB::table('krs_detail')->insert($detailInserts);
            }

            // Kembalikan model yang terbuat agar menjadi hasil dari DB::transaction
            return $createdRecord;
        });
        // Sekarang Intelephense tahu ini pasti mengembalikan Model
        return $record;
    }
}
