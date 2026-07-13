<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankKampus extends Model
{
    protected $table = 'bank_kampuses';

    protected $fillable = [
        'nama_bank',
        'no_rekening',
        'atas_nama',
        'logo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}