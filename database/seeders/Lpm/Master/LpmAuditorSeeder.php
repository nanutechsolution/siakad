<?php

declare(strict_types=1);

namespace Database\Seeders\Lpm\Master;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LpmAuditorSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        /*
         |--------------------------------------------------------------
         | Ambil maksimal 10 dosen yang memiliki person.
         | Nantinya mereka dijadikan auditor internal.
         |--------------------------------------------------------------
         */
        $persons = DB::table('trx_dosen as d')
            ->join('ref_person as p', 'p.id', '=', 'd.person_id')
            ->select(
                'p.id',
                'p.nama_lengkap'
            )
            ->orderBy('p.nama_lengkap')
            ->limit(10)
            ->get();

        $rows = [];

        foreach ($persons as $index => $person) {

            $rows[] = [

                'person_id' => $person->id,

                'no_sertifikat_auditor' =>
                'AUD-' .
                    now()->year .
                    '-' .
                    str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),

                'kompetensi' => collect([
                    'Audit Mutu Internal',
                    'SPMI',
                    'SN-DIKTI',
                    'Akreditasi BAN-PT',
                ])->join(', '),

                'is_active' => true,

                'created_at' => $now,

                'updated_at' => $now,

            ];
        }

        DB::table('lpm_auditors')->upsert(

            $rows,

            ['person_id'],

            [
                'no_sertifikat_auditor',
                'kompetensi',
                'is_active',
                'updated_at',
            ]

        );
    }
}
