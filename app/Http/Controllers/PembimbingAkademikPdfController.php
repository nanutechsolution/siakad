<?php

namespace App\Http\Controllers;

use App\Models\PembimbingAkademik;
use App\Services\PembimbingAkademikPdfService;
use Illuminate\Http\Response;

class PembimbingAkademikPdfController extends Controller
{
    public function downloadSk(
        PembimbingAkademik $pembimbingAkademik,
        PembimbingAkademikPdfService $service,
    ): Response {
        return $service->downloadSkPenugasan($pembimbingAkademik);
    }
}
