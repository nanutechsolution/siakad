<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class PdfVerification extends Model
{
    const UPDATED_AT = null;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }
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
