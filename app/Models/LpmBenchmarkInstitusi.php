<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpmBenchmarkInstitusi extends Model
{
    protected $fillable = ['nama_institusi', 'jenis', 'catatan'];

    public function benchmarks(): HasMany
    {
        return $this->hasMany(LpmBenchmark::class, 'institusi_pembanding_id');
    }
}
