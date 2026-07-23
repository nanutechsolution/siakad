<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Migration\Enums\MigrationRowStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $migration_batch_id
 * @property int $row_number
 * @property string|null $nim
 * @property string|null $mahasiswa_id
 * @property int|null $krs_detail_id
 * @property MigrationRowStatus $status
 * @property string|null $pesan
 * @property array<string, mixed> $row_data
 */
class MigrationLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'migration_batch_id',
        'row_number',
        'nim',
        'mahasiswa_id',
        'krs_detail_id',
        'status',
        'pesan',
        'row_data',
    ];

    protected function casts(): array
    {
        return [
            'status' => MigrationRowStatus::class,
            'row_data' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(MigrationBatch::class, 'migration_batch_id');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function krsDetail(): BelongsTo
    {
        return $this->belongsTo(KrsDetail::class, 'krs_detail_id');
    }
}