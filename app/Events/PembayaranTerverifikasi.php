<?php

namespace App\Events;

use App\Models\PembayaranMahasiswa;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PembayaranTerverifikasi
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly PembayaranMahasiswa $pembayaran)
    {
    }
}