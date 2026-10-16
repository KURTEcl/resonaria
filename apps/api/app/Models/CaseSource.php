<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseSource extends Model
{
    protected $fillable = [
        'case_id',
        'title',
        'url',
        'kind',
        'locator',
        'published_on',
        'is_primary',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'published_on' => 'date',
            'is_primary' => 'boolean',
        ];
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(EditorialCase::class, 'case_id');
    }

    public function toPublicArray(): array
    {
        return [
            'title' => $this->title,
            'url' => $this->url,
            'kind' => $this->kind,
            'locator' => $this->locator,
            'published_on' => $this->published_on?->toDateString(),
            'is_primary' => $this->is_primary,
        ];
    }
}
