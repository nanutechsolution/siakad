<?php

namespace App\Http\Controllers;

use App\Models\MidtransTransaction;
use Illuminate\Http\JsonResponse;

class MidtransResultController extends Controller
{
    public function index(string $orderId)
    {
        return view('midtrans.result', [
            'orderId' => $orderId,
        ]);
    }


    public function status(string $orderId): JsonResponse
    {
        $trx = MidtransTransaction::where('order_id', $orderId)
            ->first();

        if (!$trx) {
            return response()->json([
                'status' => 'not_found'
            ]);
        }


        return response()->json([
            'status' => $trx->status ?? 'pending',
        ]);
    }
}
