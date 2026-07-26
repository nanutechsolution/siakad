<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PdfSignature extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'pdf_document_id',
        'signature_authority_id',
        'person_id',
        'nama_penandatangan_snapshot',
        'jabatan_snapshot',
        'urutan',
        'document_hash_at_signing',
        'signed_at',
        'status',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->id ??= (string) Str::uuid();
        });
    }

    public function pdfDocument()
    {
        return $this->belongsTo(PdfDocument::class, 'pdf_document_id');
    }
}
