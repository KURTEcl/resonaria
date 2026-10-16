<?php

namespace App\Models;

use App\Support\PlainText;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseClaim extends Model
{
    protected $fillable = [
        'case_id',
        'claim_key',
        'claim',
        'verdict',
        'response',
        'heard_on',
        'source_url',
        'source_title',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::saving(function (CaseClaim $claim) {
            foreach (['claim', 'response'] as $field) {
                if (is_string($claim->{$field})) {
                    $claim->{$field} = PlainText::normalizeMarks($claim->{$field});
                }
            }
        });
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(EditorialCase::class, 'case_id');
    }

    public function toPublicArray(): array
    {
        return [
            'claim' => $this->claim,
            'verdict' => $this->verdict,
            'response' => $this->response,
            'heard_on' => $this->heard_on,
            'source_url' => $this->source_url,
            'source_title' => $this->source_title,
        ];
    }
}
