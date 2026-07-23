<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LpmKuisionerPertanyaan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lpm_kuisioner_pertanyaan';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    public function kelompok(): BelongsTo
    {
        return $this->belongsTo(LpmKuisionerKelompok::class, 'kelompok_id');
    }

    public function surveyJawabans(): HasMany
    {
        return $this->hasMany(LpmSurveyJawaban::class, 'pertanyaan_id');
    }

    public function jawabans(): HasMany
    {
        return $this->hasMany(LpmEdomJawaban::class, 'pertanyaan_id');
    }

    public function jawabanPihaks(): HasMany
    {
        return $this->hasMany(LpmSurveyJawabanPihak::class, 'pertanyaan_id');
    }

    public function isRating(): bool
    {
        return str_starts_with((string) $this->jenis_input, 'RATING');
    }
}
