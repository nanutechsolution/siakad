<?php

namespace App\Enums;

enum TipeTransaksiLedger: string
{
    case TAGIHAN = 'TAGIHAN';
    case PEMBAYARAN = 'PEMBAYARAN';
    case ADJUSTMENT = 'ADJUSTMENT';
    case REFUND = 'REFUND';
}
