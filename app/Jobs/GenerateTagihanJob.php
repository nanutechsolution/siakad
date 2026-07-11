<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\KeuanganKomponenBiaya;
use App\Models\Mahasiswa;
use App\Models\RefTahunAkademik;
use App\Models\User;
use App\Services\Keuangan\BeasiswaDiskonService;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateTagihanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $data;
    protected ?string $userId;

    public function __construct(array $data, ?string $userId)
    {
        $this->data = $data;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        $tahunAkademikId = $this->data['tahun_akademik_id'];
        $tahunAkademik = RefTahunAkademik::find($tahunAkademikId);
        $namaTahun = $tahunAkademik ? $tahunAkademik->nama_tahun : 'Semester Berjalan';

        // Variabel perekam status proses
        $successCount = 0;
        $failedCount = 0;
        $skippedCount = 0;
        $errorLog = []; // Menampung pesan error/kendala spesifik per mahasiswa

        $query = Mahasiswa::query();
        if ($this->data['tipe_target'] === 'kolektif') {
            if (!empty($this->data['prodi_id'])) {
                $query->where('prodi_id', $this->data['prodi_id']);
            }
            if (!empty($this->data['angkatan_id'])) {
                $query->where('angkatan_id', $this->data['angkatan_id']);
            }
        } else {
            $query->where('id', $this->data['mahasiswa_id']);
        }

        // Ambil data mahasiswa
        $mahasiswas = $query->get();

        //  Instansiasi Service Beasiswa di luar loop agar memory efisien
        $beasiswaService = app(BeasiswaDiskonService::class);

        foreach ($mahasiswas as $mhs) {
            DB::beginTransaction();
            try {
                // 1. Cek duplikasi tagihan
                $tagihanExists = DB::table('tagihan_mahasiswas')
                    ->where('mahasiswa_id', $mhs->id)
                    ->where('tahun_akademik_id', $tahunAkademikId)
                    ->exists();

                if ($tagihanExists) {
                    $skippedCount++;
                    DB::rollBack();
                    continue;
                }

                // 2. Ambil skema tarif
                $programKelasId = $mhs->program_kelas_id ?? $mhs->program_id;
                $skemaTarif = DB::table('keuangan_skema_tarif')
                    ->where('angkatan_id', $mhs->angkatan_id)
                    ->where('prodi_id', $mhs->prodi_id)
                    ->where('program_kelas_id', $programKelasId)
                    ->where('is_active', 1)
                    ->first();

                if (!$skemaTarif) {
                    throw new \Exception("Skema tarif untuk Prodi/Angkatan/Kelas mahasiswa ini belum dikonfigurasi.");
                }

                // 3. Ambil komponen tarif
                $detailTarif = DB::table('keuangan_detail_tarif')
                    ->join('keuangan_komponen_biaya', 'keuangan_komponen_biaya.id', '=', 'keuangan_detail_tarif.komponen_biaya_id')
                    ->where('keuangan_detail_tarif.skema_tarif_id', $skemaTarif->id)
                    ->select('keuangan_detail_tarif.*', 'keuangan_komponen_biaya.nama_komponen')
                    ->get();

                if ($detailTarif->isEmpty()) {
                    throw new \Exception("Komponen tarif pada skema biaya prodi ini masih kosong.");
                }

                // [BARU] Pre-load Model KeuanganKomponenBiaya untuk parameter Service (mencegah N+1)
                $komponenIds = $detailTarif->pluck('komponen_biaya_id')->toArray();
                $modelKomponens = KeuanganKomponenBiaya::whereIn('id', $komponenIds)->get()->keyBy('id');

                $tagihanId = Str::uuid()->toString();
                $kodeTransaksi = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(6));

                $totalTagihanKotor = 0;
                $totalDiskonKeseluruhan = 0;
                $detailTagihanToInsert = [];

                foreach ($detailTarif as $tarif) {
                    $nominalDasar = (float) $tarif->nominal;
                    
                    //  Kalkulasi Diskon via Service Layer
                    $komponenModel = $modelKomponens->get($tarif->komponen_biaya_id);
                    $nominalDiskonKomponen = 0.0;

                    if ($komponenModel && $tahunAkademik) {
                        $nominalDiskonKomponen = $beasiswaService->hitungDiskonUntukKomponen(
                            mahasiswa: $mhs,
                            komponen: $komponenModel,
                            tahunAkademik: $tahunAkademik,
                            nominalDasar: $nominalDasar
                        );
                    }

                    $detailTagihanToInsert[] = [
                        'tagihan_id'             => $tagihanId,
                        'komponen_biaya_id'      => $tarif->komponen_biaya_id,
                        'nama_komponen_snapshot' => $tarif->nama_komponen,
                        'nominal_dasar'          => $nominalDasar,
                        'nominal_diskon'         => $nominalDiskonKomponen,
                        'nominal_terbayar'       => 0.00,
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ];

                    $totalTagihanKotor += $nominalDasar;
                    $totalDiskonKeseluruhan += $nominalDiskonKomponen;
                }

                $totalTagihanBersih = $totalTagihanKotor - $totalDiskonKeseluruhan;
                if ($totalTagihanBersih < 0) {
                    $totalTagihanBersih = 0;
                }

                // Insert Header
                DB::table('tagihan_mahasiswas')->insert([
                    'id'                => $tagihanId,
                    'mahasiswa_id'      => $mhs->id,
                    'tahun_akademik_id' => $tahunAkademikId,
                    'kode_transaksi'    => $kodeTransaksi,
                    'deskripsi'         => "Tagihan Biaya Kuliah Mahasiswa NIM {$mhs->nim} - {$namaTahun}",
                    'total_tagihan'     => $totalTagihanBersih,
                    'total_bayar'       => 0.00,
                    'status_bayar'      => 'BELUM',
                    'created_by'        => $this->userId,
                    'tenggat_waktu'     => now()->addDays(30),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                // Insert Detail
                if (!empty($detailTagihanToInsert)) {
                    DB::table('tagihan_mahasiswas_details')->insert($detailTagihanToInsert);
                }

                DB::commit();
                $successCount++;

            } catch (\Exception $e) {
                DB::rollBack();
                $failedCount++;
                // Rekam NIM, Nama Mahasiswa (via atribut/relasi), beserta alasan error-nya
                $namaMhs = $mhs->person->nama_lengkap ?? 'Unknown';
                $errorLog[] = "NIM {$mhs->nim} ({$namaMhs}): " . $e->getMessage();
            }
        }

        // === KIRIM NOTIFIKASI DENGAN DETAIL REKAPAN ===
        if ($this->userId) {
            $admin = User::find($this->userId);
            if ($admin) {
                $bodySummary = "Hasil proses: {$successCount} Berhasil, {$failedCount} Gagal, {$skippedCount} Sudah Ada.";
                
                if (!empty($errorLog)) {
                    $bodySummary .= "\n\nBeberapa Kendala:\n" . implode("\n", array_slice($errorLog, 0, 5));
                    if (count($errorLog) > 5) {
                        $bodySummary .= "\n...dan " . (count($errorLog) - 5) . " kendala lainnya.";
                    }
                }

                Notification::make()
                    ->title('Laporan Generate Tagihan Selesai')
                    ->body($bodySummary)
                    ->status($failedCount > 0 ? 'warning' : 'success')
                    ->sendToDatabase($admin);
            }
        }
    }
}