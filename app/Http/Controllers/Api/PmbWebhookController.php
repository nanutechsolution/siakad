<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessCamabaStaging;
use App\Models\PmbCamabaStaging;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PmbWebhookController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $payload = $request->all();

        // Jika request berupa object tunggal, ubah menjadi array
        $camabas = array_is_list($payload) ? $payload : [$payload];

        // Validasi setiap item
        $validator = Validator::make($camabas, [
            '*.nomor_pendaftaran' => 'required|string|max:255',
            '*.nik'               => 'required|string|max:20',
            '*.nama_lengkap'      => 'required|string',
            '*.nama_prodi'        => 'required|string',
            '*.tahun_masuk'       => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal. Pastikan format data sesuai.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $results = [];

        foreach ($camabas as $camaba) {

            $externalId = $camaba['nomor_pendaftaran'];

            $existing = PmbCamabaStaging::where('external_id', $externalId)->first();

            if ($existing) {

                if (in_array($existing->status, ['pending', 'processing', 'processed'])) {

                    $results[] = [
                        'nomor_pendaftaran' => $externalId,
                        'status' => 'exists',
                        'staging_id' => $existing->id,
                    ];

                    continue;
                }

                $existing->update([
                    'payload' => $camaba,
                    'status' => 'pending',
                ]);

                $staging = $existing;

            } else {

                $staging = PmbCamabaStaging::create([
                    'external_id' => $externalId,
                    'payload'     => $camaba,
                    'status'      => 'pending',
                    'source'      => 'PMB',
                    'ip_address'  => $request->ip(),
                    'user_agent'  => $request->userAgent(),
                ]);
            }

            ProcessCamabaStaging::dispatch($staging);

            $results[] = [
                'nomor_pendaftaran' => $externalId,
                'status' => 'accepted',
                'staging_id' => $staging->id,
            ];
        }

        return response()->json([
            'status' => 'success',
            'message' => count($results) . ' data berhasil diproses.',
            'data' => $results,
        ], 202);
    }
}