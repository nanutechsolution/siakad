<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LppmLuaran extends Model
{
    use HasFactory;

    protected $table = 'lppm_luarans';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tahun_terbit' => 'integer',
        ];
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(TrxDosen::class, 'dosen_id');
    }

    public function jenisLuaran(): BelongsTo
    {
        return $this->belongsTo(LppmRefJenisLuaran::class, 'jenis_luaran_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
