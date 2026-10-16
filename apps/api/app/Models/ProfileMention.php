<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProfileMention extends Model
{
    protected $fillable = [
        'profile_id',
        'mentionable_type',
        'mentionable_id',
        'role',
        'origin',
        'confidence',
        'excerpt',
        'context_note',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(PublicProfile::class, 'profile_id');
    }

    public function mentionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function toDeskArray(): array
    {
        $this->loadMissing(['profile', 'mentionable']);

        $context = $this->resolveContext();

        return [
            'id' => $this->id,
            'profile_slug' => $this->profile?->slug,
            'profile_display_name' => $this->profile?->display_name,
            'role' => $this->role,
            'origin' => $this->origin,
            'confidence' => $this->confidence,
            'excerpt' => $this->excerpt,
            'context_note' => $this->context_note,
            'mentionable_type' => class_basename($this->mentionable_type),
            'mentionable_id' => $this->mentionable_id,
            'context' => $context,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array{case_slug: ?string, label: string, url: ?string}
     */
    private function resolveContext(): array
    {
        $mentionable = $this->mentionable;

        if ($mentionable instanceof EditorialCase) {
            return [
                'case_slug' => $mentionable->slug,
                'label' => $mentionable->title,
                'url' => null,
            ];
        }

        if ($mentionable instanceof CaseSource) {
            $mentionable->loadMissing('case');

            return [
                'case_slug' => $mentionable->case?->slug,
                'label' => $mentionable->title,
                'url' => $mentionable->url,
            ];
        }

        if ($mentionable instanceof CaseMilestone) {
            $mentionable->loadMissing('case');

            return [
                'case_slug' => $mentionable->case?->slug,
                'label' => $mentionable->label,
                'url' => $mentionable->source_url,
            ];
        }

        if ($mentionable instanceof Reaction) {
            $mentionable->loadMissing('editorialCase');

            return [
                'case_slug' => $mentionable->editorialCase?->slug,
                'label' => $mentionable->author_handle ?: $mentionable->url,
                'url' => $mentionable->url,
            ];
        }

        if ($mentionable instanceof CaseClaim) {
            $mentionable->loadMissing('case');

            return [
                'case_slug' => $mentionable->case?->slug,
                'label' => $mentionable->claim,
                'url' => $mentionable->source_url,
            ];
        }

        return [
            'case_slug' => null,
            'label' => (string) $this->mentionable_id,
            'url' => null,
        ];
    }
}
