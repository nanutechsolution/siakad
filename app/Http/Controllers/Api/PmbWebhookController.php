<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PmbCamabaStaging;
use App\Jobs\ProcessCamabaStaging;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class PmbWebhookController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // 1. Validasi struktur JSON dari PMB
        $validator = Validator::make($request->all(), [
            'nomor_pendaftaran' => 'required|string|max:255',
            'nik'               => 'required|string|max:20',
            'nama_lengkap'      => 'required|string',
            'nama_prodi'        => 'required|string',
            'tahun_masuk'       => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal. Pastikan format data sesuai.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $externalId = $request->input('nomor_pendaftaran');
        
        // 2. Cek Idempotency
        $existingStaging = PmbCamabaStaging::where('external_id', $externalId)->first();

        if ($existingStaging) {
            if (in_array($existingStaging->status, ['pending', 'processing', 'processed'])) {
                return response()->json([
                    'status'  => 'info',
                    'message' => "Data dengan pendaftaran {$externalId} sudah diterima.",
                    'data'    => ['staging_id' => $existingStaging->id]
                ], 200);
            }
            
            $staging = $existingStaging;
            $staging->payload = $request->all(); // Simpan seluruh JSON
            $staging->status = 'pending';
            $staging->save();
        } else {
            // 3. Simpan sebagai data baru
            $staging = PmbCamabaStaging::create([
                'external_id' => $externalId,
                'payload'     => $request->all(), 
                'status'      => 'pending',
                'source'      => 'PMB',
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ]);
        }

        // 4. Lemparkan ke Background Job
        ProcessCamabaStaging::dispatch($staging);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data PMB berhasil diterima.',
            'data'    => ['staging_id' => $staging->id]
        ], 202);
    }
}