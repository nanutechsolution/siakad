<?php

namespace App\Jobs;

use App\Models\PmbCamabaStaging;
use App\Models\RefPerson;
use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\RefAngkatan;
use App\Models\RefProdi;
use App\Models\RefProgram;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ProcessCamabaStaging implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public PmbCamabaStaging $staging) {}

    public function handle(): void
    {
        $payload = is_array($this->staging->payload)
            ? $this->staging->payload
            : json_decode($this->staging->payload, true);

        try {
            DB::beginTransaction();

            // 1. Cari Relasi ID dari Database SIAKAD berdasarkan teks dari PMB
            $prodi = RefProdi::where('kode_prodi_internal', $payload['kode_prodi'])
                ->first();

            if (!$prodi) {
                throw new \Exception("Prodi dengan nama '{$payload['kode_prodi']}' tidak ditemukan di SIAKAD.");
            }

            // Cari program (Reguler dll), default ke ID 1 jika tidak ketemu
            $program = RefProgram::where('kode_internal', $payload['kode_program'] ?? 'REG')->first();
            $programId = $program ? $program->id : 1;

            // 2. Buat Record di ref_person
            $person = RefPerson::create([
                'nama_lengkap'  => $payload['nama_lengkap'],
                'nik'           => $payload['nik'],
                'email'         => $payload['email'] ?? null,
                'no_hp'         => $payload['nomor_hp'] ?? null,
                'tanggal_lahir' => $payload['tanggal_lahir'],
                'tempat_lahir'  => $payload['tempat_lahir'] ?? null,
                'jenis_kelamin' => $payload['jenis_kelamin'] ?? null,
            ]);
            Log::info('PERSON CREATED', [
                'id' => $person->id,
                'attributes' => $person->getAttributes(),
            ]);
            // 3. Buat Akun Filament (Users)
            $passwordRaw = date('Ymd', strtotime($person->tanggal_lahir));
            $emailUser = $person->email ?? ($this->staging->external_id . '@camaba.local');
            try {
                $user = User::create([
                    'person_id' => $person->id,
                    'name'      => $person->nama_lengkap,
                    'username'  => $this->staging->external_id,
                    'email'     => $emailUser,
                    'password'  => Hash::make($passwordRaw),
                    'is_active' => 1,
                ]);
                Log::info('USER CREATED', [
                    'id' => $user->id,
                    'person_id' => $user->person_id,
                    'attributes' => $user->getAttributes(),
                ]);
                Log::info("User created successfully: " . $user->id);
            } catch (\Exception $e) {
                Log::error("USER CREATE FAILED: " . $e->getMessage());
                throw $e; // Ini akan memicu rollback dan muncul di log error
            }

            // 4. Susun Data Tambahan (Orang Tua & Sekolah)
            $dataTambahan = [
                'agama'             => $payload['agama'] ?? null,
                'alamat'            => $payload['alamat'] ?? null,
                'asal_sekolah'      => $payload['asal_sekolah'] ?? null,
                'nisn'              => $payload['nisn'] ?? null,
                'tahun_lulus'       => $payload['tahun_lulus'] ?? null,
                'jalur_pendaftaran' => $payload['jalur_pendaftaran'] ?? null,
                'orang_tua' => [
                    'ayah' => [
                        'nama'       => $payload['nama_ayah'] ?? null,
                        'nik'        => $payload['nik_ayah'] ?? null,
                        'pekerjaan'  => $payload['pekerjaan_ayah'] ?? null,
                        'pendidikan' => $payload['pendidikan_ayah'] ?? null,
                    ],
                    'ibu' => [
                        'nama'       => $payload['nama_ibu'] ?? null,
                        'nik'        => $payload['nik_ibu'] ?? null,
                        'pekerjaan'  => $payload['pekerjaan_ibu'] ?? null,
                        'pendidikan' => $payload['pendidikan_ibu'] ?? null,
                    ]
                ]
            ];
            // 4.5. Pastikan Tahun Angkatan Tersedia di Database
            $angkatanTahun = (int) $payload['tahun_masuk'];
            RefAngkatan::firstOrCreate(
                ['id_tahun' => $angkatanTahun],
                ['is_active_pmb' => 0] // Default value sesuai skema
            );
            // 5. Buat Data Mahasiswa Sementara (NIM berawalan PMB-)
            $mahasiswa = Mahasiswa::create([
                'person_id'     => $person->id,
                'nim'           => $this->staging->external_id,
                'angkatan_id'   => (int) $payload['tahun_masuk'],
                'prodi_id'      => $prodi->id,
                'program_id'    => $programId,
                'data_tambahan' => $dataTambahan,
            ]);

            // 6. Tandai Sukses
            $this->staging->update([
                'status'       => 'processed',
                'mahasiswa_id' => $mahasiswa->id,
                'processed_at' => now(),
                'error_log'    => null,
            ]);

            DB::commit();

            Log::info("Staging PMB Sukses: {$this->staging->external_id} diimpor. Prodi ID: {$prodi->id}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal proses Camaba Staging ID {$this->staging->id}: " . $e->getMessage());

            $this->staging->update([
                'status'        => 'failed',
                'error_log'     => $e->getMessage(),
                'retry_count'   => DB::raw('retry_count + 1'),
                'last_retry_at' => now(),
            ]);

            throw $e;
        }
    }
}
