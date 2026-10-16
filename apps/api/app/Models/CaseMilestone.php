<?php

namespace App\Models;

use App\Support\PlainText;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseMilestone extends Model
{
    protected $fillable = [
        'case_id',
        'occurred_on',
        'occurred_at',
        'kind',
        'label',
        'note',
        'direction',
        'source_url',
        'source_title',
        'procedural_status',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::saving(function (CaseMilestone $milestone) {
            foreach (['label', 'note'] as $field) {
                if (is_string($milestone->{$field})) {
                    $milestone->{$field} = PlainText::normalizeMarks($milestone->{$field});
                }
            }
        });
    }

    protected function casts(): array
    {
        return [
            'occurred_on' => 'date',
            'occurred_at' => 'datetime',
        ];
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(EditorialCase::class, 'case_id');
    }

    public function toPublicArray(): array
    {
        return [
            'occurred_on' => $this->occurred_on->toDateString(),
            'occurred_at' => $this->occurred_at?->toIso8601String(),
            'kind' => $this->kind,
            'label' => $this->label,
            'note' => $this->note,
            'direction' => $this->direction ?: 'incierto',
            'source_url' => $this->source_url,
            'source_title' => $this->source_title,
            'procedural_status' => $this->procedural_status,
        ];
    }
}
