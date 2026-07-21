<?php

namespace App\Models\LaporanKeuangan;

use Illuminate\Database\Eloquent\Model;

class LaporanAgregatRecord extends Model
{
    protected $table = 'laporan';

    public $timestamps = false;

    protected $guarded = [];

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';
}
