<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PdfDocument extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'document_type',
        'classification',
        'documentable_type',
        'documentable_id',
        'nomor_dokumen',
        'file_disk',
        'file_path',
        'file_hash',
        'version',
        'is_current',
        'status',
        'metadata',
        'generated_by',
        'generated_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_current' => 'boolean',
        'generated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->id ??= (string) Str::uuid();
        });
    }

    public function documentable()
    {
        return $this->morphTo();
    }

    public function signatures()
    {
        return $this->hasMany(PdfSignature::class, 'pdf_document_id')->orderBy('urutan');
    }
}
