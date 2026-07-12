<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AkademikGradeRevisionLog extends Model
{
    protected $table = 'akademik_grade_revision_logs';

    protected $fillable = [
        'krs_detail_id',
        'old_nilai_angka',
        'old_nilai_huruf',
        'new_nilai_angka',
        'new_nilai_huruf',
        'alasan_perbaikan',
        'nomor_sk_perbaikan',
        'executed_by',
    ];

    protected $casts = [
        'old_nilai_angka' => 'decimal:2',
        'new_nilai_angka' => 'decimal:2',
    ];

    /**
     * Detail KRS (mata kuliah yang diambil mahasiswa) yang nilainya direvisi.
     */
    public function krsDetail(): BelongsTo
    {
        return $this->belongsTo(KrsDetail::class, 'krs_detail_id');
    }

    /**
     * User (dosen/admin) yang melakukan eksekusi revisi nilai ini.
     */
    public function executedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }
}
