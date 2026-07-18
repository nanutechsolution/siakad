<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SinkronisasiExportDownloadController extends Controller
{
    public function __invoke(Request $request, int $export)
    {
        abort_unless($request->user()?->can('SinkronisasiTagihan'), 403);

        $row = DB::table('exports')->where('id', $export)->first();
        abort_if($row === null || $row->file_name === null, 404);

        // Hanya pemilik export sendiri yang boleh unduh, kecuali user
        // punya permission tambahan untuk melihat export siapa pun -
        // sesuaikan dengan kebijakan otorisasi project Anda.
        abort_unless(
            $row->user_id === $request->user()->id || $request->user()->can('SinkronisasiTagihan.viewAny'),
            403
        );

        $disk = Storage::disk($row->file_disk ?? 'local');
        abort_unless($disk->exists($row->file_name), 404);

        return $disk->download($row->file_name, 'sinkronisasi-preview.csv');
    }
}
