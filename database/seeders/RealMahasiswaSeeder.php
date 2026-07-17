<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use App\Models\RefProdi as Prodi;
use App\Models\RefProgram as ProgramKelas;
use App\Models\User;
use App\Helpers\SistemHelper;
use App\Models\RefPerson as ModelsPerson;
use App\Models\Mahasiswa;
use Carbon\Carbon;
use App\Models\RiwayatStatusMahasiswa;
use Symfony\Component\Console\Helper\ProgressBar;

class RealMahasiswaSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Memulai import SEMUA data Mahasiswa Real dari CSV...');
        $limit = 30; // jumlah maksimal mahasiswa yang mau diimport
        $startAngkatan = 2022; // angkatan minimal yang mau diimport
        $csvPath = database_path('csv/mahasiswa_real.csv');

        if (!File::exists($csvPath)) {
            $this->command->error("❌ File tidak ditemukan: {$csvPath}");
            return;
        }

        // --- SETUP FILE ERROR LOG ---
        $errorLogPath = storage_path('app/failed_import_mahasiswa.csv');
        $errorFile = fopen($errorLogPath, 'w');
        fputcsv($errorFile, ['NIM', 'Nama', 'Status', 'Pesan Error/Keterangan', 'Data Baris Asli']);

        // --- HITUNG TOTAL BARIS UNTUK PROGRESS BAR ---
        $totalRows = 0;
        $handle = fopen($csvPath, "r");
        while (!feof($handle)) {
            fgets($handle);
            $totalRows++;
        }
        fclose($handle);
        $totalRows -= 1; // Kurangi 1 untuk baris header

        $file = fopen($csvPath, 'r');
        $headerFound = false;

        $countImport = 0;
        $countSkip   = 0;
        $countError  = 0;
        // Variabel $limit sudah dihapus

        $prodis = Prodi::pluck('id', 'nama_prodi');

        $kelasReguler = ProgramKelas::firstOrCreate(
            ['kode_internal' => 'REG'],
            [
                'nama_program' => 'Reguler Pagi',
                'min_pembayaran_persen' => 25,
                'is_active' => true
            ]
        );
        $kelasRegulerId = $kelasReguler->id;

        $taAktifId = SistemHelper::idTahunAktif();

        // Progress bar menggunakan total baris asli dari file CSV
        $progress = new ProgressBar($this->command->getOutput(), $totalRows);
        $progress->setFormat(" %cur t%/%max% [%bar%] %percent:3s%% | %message%");
        $progress->start();
        $limit = 30;
        DB::beginTransaction();
        try {
            // Looping sekarang membaca sampai baris terakhir CSV tanpa batasan
            while (($row = fgetcsv($file)) !== false) {
                if ($countImport >= $limit) {
                    break;
                }
                // Deteksi header
                if (!$headerFound) {
                    $rowStr = implode(',', $row);
                    if (stripos($rowStr, 'NIM') !== false && stripos($rowStr, 'Nama') !== false) {
                        $headerFound = true;
                    }
                    continue;
                }

                if (count($row) < 5 || empty(trim($row[1]))) {
                    $countSkip++;
                    $progress->setMessage("⚠ [SKIP] Baris kosong / invalid");
                    fputcsv($errorFile, ['-', '-', 'SKIP', 'Baris kosong atau invalid', implode(',', $row)]);
                    $progress->advance();
                    continue;
                }

                $nim = null;
                $nama = null;

                try {
                    // --- Mapping CSV ---
                    $nim          = $this->cleanString($row[1]);
                    $nik          = $this->cleanNumber($row[2]);
                    $nama         = $this->cleanString($row[3]);
                    $prodiCsv     = $this->cleanString($row[4]);
                    $tglMasuk     = $this->cleanString($row[5]);
                    $angkatan     = (int) $this->cleanNumber($row[6]);
                    if ($angkatan < $startAngkatan) {
                        $countSkip++;
                        $progress->setMessage("⚠ [SKIP] Angkatan {$angkatan} kurang dari {$startAngkatan}");
                        fputcsv($errorFile, [$nim, $nama, 'SKIP', "Angkatan {$angkatan} < {$startAngkatan}", implode(',', $row)]);
                        $progress->advance();
                        continue;
                    }
                    $jenisDaftar  = $this->cleanString($row[7]);
                    $biayaMasuk   = $this->cleanNumber($row[8]);
                    $gender       = strtoupper(trim($row[9])) == 'P' ? 'P' : 'L';
                    $ttlRaw       = $this->cleanString($row[10]);
                    $agama        = $this->cleanString($row[11]);
                    $alamat       = $this->cleanString($row[12] ?? '');

                    // Cek NIM duplikat
                    if (Mahasiswa::where('nim', $nim)->exists()) {
                        $countSkip++;
                        $progress->setMessage("⚠ [SKIP] NIM {$nim} sudah ada");
                        fputcsv($errorFile, [$nim, $nama, 'SKIP', 'NIM sudah ada di database', implode(',', $row)]);
                        $progress->advance();
                        continue;
                    }

                    // Pastikan Tahun Angkatan ada
                    if ($angkatan > 1900) {
                        DB::table('ref_angkatan')->updateOrInsert(
                            ['id_tahun' => $angkatan],
                            ['updated_at' => now(), 'created_at' => now()]
                        );
                    } else {
                        $angkatan = (int) date('Y');
                        DB::table('ref_angkatan')->updateOrInsert(['id_tahun' => $angkatan]);
                    }

                    // Mapping Prodi
                    $prodiId = $prodis[$prodiCsv] ?? null;
                    if (!$prodiId) {
                        $cleanName = trim(str_replace(['S1', 'D3', 'D4'], '', $prodiCsv));
                        $prodiDb = Prodi::where('nama_prodi', 'like', "%$cleanName%")->first();
                        $prodiId = $prodiDb ? $prodiDb->id : Prodi::first()->id ?? 1;
                    }

                    // Parsing TTL
                    $tempatLahir = null;
                    $tglLahir = null;
                    if (str_contains($ttlRaw, ',')) {
                        $parts = explode(',', $ttlRaw);
                        $tempatLahir = trim($parts[0]);
                        try {
                            $dateStr = str_ireplace(
                                ['Mei', 'Agustus', 'Oktober', 'Desember', 'Januari', 'Februari', 'Maret', 'April', 'Juni', 'Juli', 'September', 'November'],
                                ['May', 'August', 'October', 'December', 'January', 'February', 'March', 'April', 'June', 'July', 'September', 'November'],
                                trim($parts[1] ?? '')
                            );
                            $tglLahir = Carbon::parse($dateStr)->format('Y-m-d');
                        } catch (\Exception $e) {
                            $tglLahir = null;
                        }
                    } elseif (!empty($ttlRaw)) {
                        $tempatLahir = $ttlRaw;
                    }

                    // --- Simpan Person ---
                    if (ModelsPerson::where('nik', $nik)->exists() || empty($nik)) {
                        $nik = rand(1000000000000000, 9999999999999999);
                    }
                    $emailMahasiswa = $nim . '@student.unmarissumba.ac.id';
                    $person = ModelsPerson::firstOrCreate(
                        ['email' => $emailMahasiswa],
                        [
                            'nik' => $nik,
                            'nama_lengkap' => $nama,
                            'jenis_kelamin' => $gender,
                            'tempat_lahir' => $tempatLahir,
                            'tanggal_lahir' => $tglLahir,
                            'email' => $nim . '@student.unmarissumba.ac.id',
                            'no_hp' => '081234567890',
                            'updated_at' => now()
                        ]
                    );

                    // --- Simpan Mahasiswa ---
                    $mhs = Mahasiswa::create([
                        'person_id' => $person->id,
                        'nim' => $nim,
                        'angkatan_id' => $angkatan,
                        'prodi_id' => $prodiId,
                        'program_id' => 1,

                    ]);
                    // --- PASANG BLOK KODE RIWAYAT DI SINI ---
                    $tahunSekarang = (int) date('Y');

                    for ($thn = $angkatan; $thn <= $tahunSekarang; $thn++) {
                        // Cari ID untuk Semester Ganjil
                        $taGanjil = \App\Models\RefTahunAkademik::where('kode_tahun', $thn . '1')->first();
                        if ($taGanjil) {
                            \App\Models\RiwayatStatusMahasiswa::updateOrCreate(
                                ['mahasiswa_id' => $mhs->id, 'tahun_akademik_id' => $taGanjil->id],
                                ['status_kuliah' => 'A', 'created_at' => now(), 'updated_at' => now()]
                            );
                        }

                        // Cari ID untuk Semester Genap
                        $taGenap = \App\Models\RefTahunAkademik::where('kode_tahun', $thn . '2')->first();
                        if ($taGenap) {
                            \App\Models\RiwayatStatusMahasiswa::updateOrCreate(
                                ['mahasiswa_id' => $mhs->id, 'tahun_akademik_id' => $taGenap->id],
                                ['status_kuliah' => 'A', 'created_at' => now(), 'updated_at' => now()]
                            );
                        }
                    }
                    // --- AKHIR PASANG BLOK KODE ---

                    // --- Buat User Login ---
                    if (!User::where('username', $nim)->exists()) {
                        $user = User::create([
                            'name' => $nama,
                            'username' => $nim,
                            'email' => $person->email,
                            'password' => Hash::make($nim),
                            'is_active' => true,
                            'person_id' => $person->id
                        ]);
                    }

                    $countImport++;
                    $progress->setMessage("✅ [SUKSES] {$nim} | {$nama}");
                } catch (\Exception $e) {
                    $countError++;
                    $errorMessage = $e->getMessage();
                    $progress->setMessage("❌ [ERROR] Baris {$countImport}: " . $errorMessage);

                    fputcsv($errorFile, [
                        $nim ?? 'UNKNOWN',
                        $nama ?? 'UNKNOWN',
                        'ERROR',
                        $errorMessage,
                        implode(',', $row)
                    ]);
                }

                $progress->advance();
            }

            DB::commit();
            $progress->finish();
            $this->command->line("\n🟢 Import selesai. Total SUKSES: {$countImport}, SKIP: {$countSkip}, ERROR: {$countError}");
            $this->command->info("📄 Log data yang gagal/skip disimpan di: {$errorLogPath}");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ GAGAL SYSTEM: " . $e->getMessage());
        }

        fclose($file);
        fclose($errorFile);
    }

    private function cleanNumber($val)
    {
        $val = trim((string)$val);
        if (empty($val) || strtolower($val) == 'null' || $val == '-') return null;
        if (preg_match('/E\+/i', $val)) {
            $val = number_format((float)$val, 0, '', '');
        }
        $val = preg_replace('/[^0-9]/', '', $val);
        return $val === '' ? null : $val;
    }

    private function cleanString($val)
    {
        if (empty($val) || $val == '-') return '';
        return trim(iconv('UTF-8', 'UTF-8//IGNORE', $val));
    }
}
