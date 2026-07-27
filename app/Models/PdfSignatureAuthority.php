<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdfSignatureAuthority extends Model
{
    protected $fillable = [
        'document_type',
        'jabatan_id',
        'urutan',
        'label',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function jabatan()
    {
        return $this->belongsTo(\App\Models\RefJabatan::class, 'jabatan_id');
    }
}
