<?php

namespace App\Http\Controllers;

use App\Services\PresensiMahasiswaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PresensiCheckinController extends Controller
{
    public function store(Request $request, PresensiMahasiswaService $service): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:10'],
            'device_fingerprint' => ['nullable', 'string', 'max:64'],
        ]);

        try {
            $service->checkin(
                $request->user(),
                $data['token'],
                $request->ip(),
                $data['device_fingerprint'] ?? null,
            );
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first()], 422);
        }

        return response()->json(['message' => 'Presensi berhasil dicatat.']);
    }
}
