<?php

namespace Database\Seeders;

use App\Models\RefPerson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\RefProdi as Prodi;
use App\Models\TrxDosen as Dosen;
use Carbon\Carbon;

class RealDosenSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Memulai import data Dosen Real dari CSV...');

        $csvPath = database_path('csv/dosen_real.csv');

        if (!File::exists($csvPath)) {
            $this->command->error("File tidak ditemukan: {$csvPath}");
            return;
        }

        $file = fopen($csvPath, 'r');
        $headerFound = false;
        $countImport = 0;
        $countSkip = 0;

        $prodis = Prodi::pluck('id', 'nama_prodi');

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($file)) !== false) {
                // 1. Deteksi Header
                if (!$headerFound) {
                    $rowStr = implode(',', $row);
                    if (stripos($rowStr, 'NIDN') !== false && stripos($rowStr, 'Nama') !== false) {
                        $headerFound = true;
                    }
                    continue;
                }

                if (count($row) < 4 || empty(trim($row[3]))) continue;

                $nama = $this->cleanString($row[3]);

                // 2. Cleaning Data (Scientific Notation & Spaces)
                $nidn   = $this->cleanNumber($row[1]);
                $nuptk  = $this->cleanNumber($row[2]);
                $prodiNameCsv = $this->cleanString($row[4]);
                $gender = (strtoupper(trim($row[5])) == 'P') ? 'P' : 'L';
                $ttlRaw = $this->cleanString($row[6]);
                $agama  = $this->cleanString($row[7] ?? '');

                // ---------------------------------------------------------
                // LOGIKA UTAMA: CEK DUPLIKASI & SKIP
                // ---------------------------------------------------------

                // Cek 1: Jika punya NIDN, cek apakah sudah ada?
                if (!empty($nidn)) {
                    if (Dosen::where('nidn', $nidn)->exists()) {
                        $this->command->warn("[SKIP] Dosen NIDN {$nidn} ({$nama}) sudah ada.");
                        $countSkip++;
                        continue; // Lanjut ke baris berikutnya
                    }
                }
                // Cek 2: Jika tidak punya NIDN tapi punya NUPTK, cek apakah sudah ada?
                elseif (!empty($nuptk)) {
                    if (Dosen::where('nuptk', $nuptk)->exists()) {
                        $this->command->warn("[SKIP] Dosen NUPTK {$nuptk} ({$nama}) sudah ada.");
                        $countSkip++;
                        continue; // Lanjut ke baris berikutnya
                    }
                }
                // Cek 3: Jika keduanya kosong, kita skip saja demi keamanan data
                else {
                    $this->command->warn("[SKIP] Dosen {$nama} tidak memiliki NIDN/NUPTK.");
                    $countSkip++;
                    continue;
                }

                // ---------------------------------------------------------
                // JIKA LOLOS CEK DI ATAS, PROSES INSERT DATA BARU
                // ---------------------------------------------------------

                // 3. Logika Prodi
                $prodiId = $prodis[$prodiNameCsv] ?? null;
                if (!$prodiId) {
                    $cleanName = trim(str_replace(['S1', 'D3', 'D4'], '', $prodiNameCsv));
                    $prodiDb = Prodi::where('nama_prodi', 'like', "%$cleanName%")->first();
                    $prodiId = $prodiDb ? $prodiDb->id : 1;
                }

                // 4. Parsing TTL
                $tempatLahir = null;
                $tglLahir = null;
                if (str_contains($ttlRaw, ',')) {
                    $parts = explode(',', $ttlRaw);
                    $tempatLahir = trim($parts[0]);
                    try {
                        $dateStr = str_ireplace(
                            ['Mei', 'Agustus', 'Oktober', 'Desember'],
                            ['May', 'August', 'October', 'December'],
                            trim($parts[1] ?? '')
                        );
                        $tglLahir = Carbon::parse($dateStr)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $tglLahir = null;
                    }
                }

                // 5. Simpan Person (Cek by Nama agar tidak duplikat orang)
                $person = RefPerson::firstOrCreate(
                    ['nama_lengkap' => $nama],
                    [
                        'jenis_kelamin' => $gender,
                        'tempat_lahir' => $tempatLahir,
                        'tanggal_lahir' => $tglLahir,
                        'email' => ($nidn ?: ($nuptk ?: rand(1000, 9999))) . '@dosen.unmaris.ac.id',
                        'updated_at' => now()
                    ]
                );

                // 6. Simpan Dosen
                $jenisDosen = (!empty($nidn)) ? 'TETAP' : 'LB';

                Dosen::create([
                    'person_id' => $person->id,
                    'prodi_id' => $prodiId,
                    'jenis_dosen' => $jenisDosen,
                    'is_active' => true,
                    'nidn' => $nidn,    // Kosong jika null
                    'nuptk' => $nuptk,  // Kosong jika null
                    'data_tambahan' => ['agama' => $agama]
                ]);

                // 7. Buat User Login
                $username = $nidn ?: $nuptk;
                $username = preg_replace('/[^A-Za-z0-9]/', '', $username);

                if (!User::where('username', $username)->exists()) {
                    $user = User::create([
                        'name' => $nama,
                        'username' => $username,
                        'email' => $person->email,
                        'password' => Hash::make($username), // Default password = Username
                        'is_active' => true,
                        'person_id' => $person->id
                    ]);
                }

                $countImport++;
            }

            DB::commit();
            $this->command->info("SELESAI. Diimport: {$countImport}, Di-skip (Duplikat): {$countSkip}.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("GAGAL SYSTEM: " . $e->getMessage());
        }

        fclose($file);
    }

    private function cleanNumber($val)
    {
        $val = trim((string)$val);
        if (empty($val) || strtolower($val) == 'null') return null;

        // Fix Scientific Notation (6.43E+15 -> 643...)
        if (preg_match('/E\+/i', $val)) {
            $val = number_format((float)$val, 0, '', '');
        }

        $val = preg_replace('/\.0+$/', '', $val);
        $val = preg_replace('/[^0-9]/', '', $val);

        // Jika hasilnya string kosong setelah dibersihkan, kembalikan null
        return $val === '' ? null : substr($val, 0, 40);
    }

    private function cleanString($val)
    {
        if (empty($val)) return '';
        return trim(iconv('UTF-8', 'UTF-8//IGNORE', $val));
    }
}
