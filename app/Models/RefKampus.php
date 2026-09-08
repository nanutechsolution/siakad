<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefKampus extends Model
{
    use HasFactory;

    protected $table = 'ref_kampus';
    protected $guarded = [];

    public function ruang()
    {
        return $this->hasMany(RefRuang::class, 'kampus_id');
    }
}
