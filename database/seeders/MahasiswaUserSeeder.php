<?php

namespace Database\Seeders;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MahasiswaUserSeeder extends Seeder
{
    public function run(): void
    {
        $total = 0;

        Mahasiswa::with('person')
            ->whereNotNull('person_id')
            ->chunkById(200, function ($mahasiswas) use (&$total) {

                foreach ($mahasiswas as $mahasiswa) {

                    if (! $mahasiswa->person) {
                        continue;
                    }

                    User::firstOrCreate(
                        [
                            'person_id' => $mahasiswa->person_id,
                        ],
                        [
                            'id' => (string) Str::uuid(),
                            'name' => $mahasiswa->person->nama_lengkap,
                            'username' => $mahasiswa->nim,
                            'email' => $mahasiswa->nim . '@student.unmaris.ac.id',
                            'password' => Hash::make($mahasiswa->nim),
                            'is_active' => true,
                        ]
                    );

                    $total++;
                }
            });

        $this->command->info("Berhasil membuat {$total} user mahasiswa.");
    }
}
