<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdfVerification extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'pdf_document_id',
        'nomor_dokumen_diminta',
        'ditemukan',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'ditemukan' => 'boolean',
        'created_at' => 'datetime',
    ];
}
