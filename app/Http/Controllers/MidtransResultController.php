<?php

namespace App\Http\Controllers;

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
        $transaction = MidtransTransaction::where('order_id', $orderId)
            ->first();

        if (! $transaction) {
            return response()->json([
                'status' => 'not_found'
            ]);
        }


        $pembayaran = PembayaranMahasiswa::where('tagihan_id', $transaction->tagihan_id)
            ->where('tagihan_type', $transaction->tagihan_type)
            ->latest()
            ->first();


        if (! $pembayaran) {
            return response()->json([
                'status' => 'pending'
            ]);
        }


        return response()->json([
            'status' => $pembayaran->status_verifikasi_id == 2
                ? 'settlement'
                : 'pending',
        ]);
    }
}
