<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LpmAmiChecklistItemSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $checklists = DB::table('lpm_ami_checklists')->get();

        $items = [];

        foreach ($checklists as $checklist) {
            $items[] = [
                'checklist_id' => $checklist->id,
                'pertanyaan'   => 'Apakah dokumen standar tersedia dan terdokumentasi?',
                'urutan'       => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];

            $items[] = [
                'checklist_id' => $checklist->id,
                'pertanyaan'   => 'Apakah implementasi telah sesuai dengan standar yang ditetapkan?',
                'urutan'       => 2,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];

            $items[] = [
                'checklist_id' => $checklist->id,
                'pertanyaan'   => 'Apakah tersedia bukti pendukung yang valid?',
                'urutan'       => 3,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];

            $items[] = [
                'checklist_id' => $checklist->id,
                'pertanyaan'   => 'Apakah ditemukan ketidaksesuaian (temuan) selama audit?',
                'urutan'       => 4,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];

            $items[] = [
                'checklist_id' => $checklist->id,
                'pertanyaan'   => 'Apakah telah disusun rencana tindak lanjut atas hasil audit?',
                'urutan'       => 5,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        DB::table('lpm_ami_checklist_items')->upsert(
            $items,
            ['checklist_id', 'urutan'],
            [
                'pertanyaan',
                'updated_at',
            ]
        );
    }
}