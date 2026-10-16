<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseLink extends Model
{
    protected $fillable = [
        'from_case_id',
        'to_case_id',
        'relation',
        'note',
        'origin',
    ];

    public function fromCase(): BelongsTo
    {
        return $this->belongsTo(EditorialCase::class, 'from_case_id');
    }

    public function toCase(): BelongsTo
    {
        return $this->belongsTo(EditorialCase::class, 'to_case_id');
    }

    /**
     * @return array{slug: string, title: string, hook: string|null, relation: string, note: string|null}
     */
    public function toOutgoingPublicArray(): array
    {
        $this->loadMissing('toCase');

        return $this->publicArrayFrom($this->toCase);
    }

    /**
     * @return array{slug: string, title: string, hook: string|null, relation: string, note: string|null}
     */
    public function toIncomingPublicArray(): array
    {
        $this->loadMissing('fromCase');

        return $this->publicArrayFrom($this->fromCase);
    }

    /**
     * @return array{slug: string, title: string, hook: string|null, relation: string, note: string|null}
     */
    private function publicArrayFrom(?EditorialCase $case): array
    {
        return [
            'slug' => $case?->slug ?? '',
            'title' => $case?->title ?? '',
            'hook' => $case?->hook,
            'relation' => $this->relation,
            'note' => $this->note,
        ];
    }
}
