<?php

namespace App\Http\Controllers;

use App\Services\Pdf\PdfVerifier;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PdfVerificationController extends Controller
{
    public function show(Request $request, string $document, PdfVerifier $verifier): View
    {
        $result = $verifier->verify($document, $request->ip(), $request->userAgent());

        return view('pdf.verify', $result);
    }
}
