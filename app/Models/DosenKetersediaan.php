<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DosenKetersediaan extends Model
{
    protected $guarded = ['id'];

    public function dosen()
    {
        return $this->belongsTo(TrxDosen::class, 'dosen_id');
    }
}
