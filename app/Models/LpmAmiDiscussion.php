<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpmAmiDiscussion extends Model
{
    protected $table = 'lpm_ami_discussions';

    protected $fillable = [
        'finding_id',
        'user_id',
        'message',
        'attachment_path',
    ];

    public function finding(): BelongsTo
    {
        return $this->belongsTo(LpmAmiFinding::class, 'finding_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}