<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpmAmiChecklistItem extends Model
{
    protected $table = 'lpm_ami_checklist_items';

    protected $fillable = [
        'checklist_id',
        'pertanyaan',
        'urutan',
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(LpmAmiChecklist::class, 'checklist_id');
    }

    public function jawabans(): HasMany
    {
        return $this->hasMany(LpmAmiChecklistJawaban::class, 'checklist_item_id');
    }
}
