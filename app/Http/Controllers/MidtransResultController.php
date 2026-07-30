<?php

namespace App\Http\Controllers;

use App\Enums\StatusVerifikasiPembayaran;
use App\Models\MidtransTransaction;
use App\Models\PembayaranMahasiswa;
use Illuminate\Http\JsonResponse;

class MidtransResultController extends Controller
{
    public function index(string $orderId)
    {
        return view('midtrans.result', [
            'orderId' => $orderId,
        ]);
    }


    public function status(string $orderId)
    {
        $pembayaran = PembayaranMahasiswa::byMidtransOrder($orderId)
            ->latest()
            ->first();


        if (!$pembayaran) {
            return response()->json([
                'status' => 'pending',
            ]);
        }


        return response()->json([
            'status' => match ($pembayaran->status_verifikasi_id) {

                StatusVerifikasiPembayaran::VERIFIED
                => 'settlement',

                StatusVerifikasiPembayaran::REJECTED
                => 'failed',

                default
                => 'pending',
            },
        ]);
    }
}
