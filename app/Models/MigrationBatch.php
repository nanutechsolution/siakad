<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Migration\Enums\MigrationBatchStatus;
use App\Domain\Migration\Enums\MigrationSource;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property MigrationSource $source
 * @property MigrationBatchStatus $status
 * @property string|null $file_name
 * @property string|null $file_path
 * @property array<string, mixed> $parameter_snapshot
 * @property array<string, mixed>|null $summary_snapshot
 * @property int $total_rows
 * @property int $total_berhasil
 * @property int $total_gagal
 * @property int $total_dilewati
 * @property string|null $error_message
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property string|null $created_by
 */
class MigrationBatch extends Model
{
    protected $fillable = [
        'source',
        'status',
        'file_name',
        'file_path',
        'parameter_snapshot',
        'summary_snapshot',
        'total_rows',
        'total_berhasil',
        'total_gagal',
        'total_dilewati',
        'error_message',
        'started_at',
        'completed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'source' => MigrationSource::class,
            'status' => MigrationBatchStatus::class,
            'parameter_snapshot' => 'array',
            'summary_snapshot' => 'array',
            'total_rows' => 'integer',
            'total_berhasil' => 'integer',
            'total_gagal' => 'integer',
            'total_dilewati' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(MigrationLog::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected function executionTimeSeconds(): Attribute
    {
        return Attribute::get(function (): ?int {
            if (! $this->started_at || ! $this->completed_at) {
                return null;
            }

            return (int) $this->completed_at->diffInSeconds($this->started_at);
        });
    }
}
