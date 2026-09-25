<?php

namespace App\Http\Controllers;

use App\Enums\StatusVerifikasiPembayaran;
use App\Models\MidtransTransaction;
use App\Models\PembayaranMahasiswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class MidtransResultController extends Controller
{
    public function index(Request $request, string $orderId)
    {
        // Halaman hasil pembayaran mengarahkan user ke pembayaran terkait,
        // jadi orderId yang diterima harus dimiliki oleh pemanggil.
        $this->assertOrderAccessible($request, $orderId);

        return view('midtrans.result', [
            'orderId' => $orderId,
        ]);
    }


    public function status(Request $request, string $orderId)
    {
        $this->assertOrderAccessible($request, $orderId);

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

    /**
     * Endpoint ini menyebut status pembayaran berdasarkan orderId yang
     * dikirim client, jadi tanpa cek pemilik siapa pun yang login bisa
     * memeriksa status pembayaran mahasiswa lain. MidtransTransaction dibuat
     * saat initiate (sebelum pembayaran terverifikasi), jadi menjadi sumber
     * kebenaran pemilik yang valid di seluruh alur.
     */
    private function assertOrderAccessible(Request $request, string $orderId): void
    {
        $user = $request->user();

        abort_if($user === null, 401);

        if ($user->hasAnyRole([
            'super_admin',
            'Admin Keuangan',
            'Kasir',
            'Verifikator Pembayaran',
        ])) {
            return;
        }

        $transaction = MidtransTransaction::query()
            ->where('order_id', $orderId)
            ->first();

        $owned = $transaction !== null
            && $transaction->mahasiswa_id !== null
            && $transaction->mahasiswa_id === ($user->person?->mahasiswa?->id);

        if (! $owned) {
            throw new AccessDeniedHttpException('Anda tidak memiliki akses ke pembayaran tersebut.');
        }
    }
}
