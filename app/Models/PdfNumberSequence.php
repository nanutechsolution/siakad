<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdfNumberSequence extends Model
{
    protected $fillable = [
        'document_type',
        'kode_unit',
        'periode_tahun',
        'last_sequence',
        'format_template',
    ];
}
