<?php

namespace App\Models;

use App\Support\CaseProfileSummary;
use App\Support\PlainText;
use App\Support\SiteUrl;
use App\Support\TopicLinks;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EditorialCase extends Model
{
    protected $table = 'cases';

    protected $fillable = [
        'category_id',
        'slug',
        'title',
        'hook',
        'summary',
        'what_changes',
        'who_affected',
        'areas',
        'change_types',
        'procedural_status',
        'public_status_label',
        'evidence_status',
        'observed_effect',
        'publication_status',
        'content_version',
        'sources_count',
        'before_text',
        'after_text',
        'detail',
        'meta_title',
        'meta_description',
        'og_image_url',
        'published_at',
        'reviewed_at',
        'next_review_at',
    ];

    protected static function booted(): void
    {
        static::saving(function (EditorialCase $case) {
            foreach ([
                'hook',
                'summary',
                'what_changes',
                'who_affected',
                'before_text',
                'after_text',
                'detail',
                'meta_description',
            ] as $field) {
                if (is_string($case->{$field})) {
                    $case->{$field} = PlainText::normalizeMarks($case->{$field});
                }
            }
        });

        static::saved(function (EditorialCase $case) {
            TopicLinks::syncFromMarkup($case);
        });
    }

    protected function casts(): array
    {
        return [
            'areas' => 'array',
            'change_types' => 'array',
            'published_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'next_review_at' => 'datetime',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('publication_status', 'published');
    }

    /** Seguimiento de decisión pública (ley, presupuesto, trámite). */
    public function scopeDecisionFollowUp(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereJsonLength('change_types', '>', 0)
                ->orWhere('procedural_status', '!=', 'en_tramitacion')
                ->orWhere(function (Builder $inner) {
                    $inner->whereNotNull('evidence_status')
                        ->where('evidence_status', '!=', 'unknown');
                })
                ->orWhere(function (Builder $inner) {
                    $inner->whereNotNull('observed_effect')
                        ->where('observed_effect', '!=', 'unknown');
                })
                ->orWhereHas('milestones');
        });
    }

    public function scopeActualidad(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where(function (Builder $inner) {
                $inner->whereNull('change_types')
                    ->orWhereJsonLength('change_types', 0);
            })
                ->where('procedural_status', 'en_tramitacion')
                ->where(function (Builder $inner) {
                    $inner->whereNull('evidence_status')
                        ->orWhere('evidence_status', 'unknown');
                })
                ->where(function (Builder $inner) {
                    $inner->whereNull('observed_effect')
                        ->orWhere('observed_effect', 'unknown');
                })
                ->whereDoesntHave('milestones');
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class, 'case_id');
    }

    public function sources(): HasMany
    {
        return $this->hasMany(CaseSource::class, 'case_id')->orderBy('sort_order');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(CaseMilestone::class, 'case_id')
            ->orderByRaw('COALESCE(occurred_at, occurred_on) ASC')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(CaseClaim::class, 'case_id')->orderBy('sort_order')->orderBy('id');
    }

    public function topicFollows(): HasMany
    {
        return $this->hasMany(TopicFollow::class, 'case_id');
    }

    public function outgoingLinks(): HasMany
    {
        return $this->hasMany(CaseLink::class, 'from_case_id')
            ->whereHas('toCase', fn (Builder $query) => $query->published())
            ->orderBy('id');
    }

    public function incomingLinks(): HasMany
    {
        return $this->hasMany(CaseLink::class, 'to_case_id')
            ->whereHas('fromCase', fn (Builder $query) => $query->published())
            ->orderBy('id');
    }

    public function toPublicListArray(): array
    {
        $this->loadMissing(['category']);

        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'hook' => $this->hook,
            'category' => $this->category?->toPublicArray(),
            'change_types' => $this->change_types ?? [],
            'public_status_label' => $this->public_status_label,
            'evidence_status' => $this->evidence_status,
            'observed_effect' => $this->observed_effect,
            'next_review_at' => $this->next_review_at?->toDateString(),
            'published_at' => $this->published_at?->toDateString(),
            'reviewed_at' => $this->reviewed_at?->toDateString(),
            'reactions_count' => (int) ($this->reactions_count ?? 0),
            'sources_count' => (int) ($this->sources_count ?? 0),
            'claims_count' => (int) ($this->claims_count ?? 0),
            'pageviews' => (int) ($this->pageviews ?? 0),
            'has_comparison' => filled($this->before_text) && filled($this->after_text),
            'cover_image_url' => $this->og_image_url,
            'profiles' => CaseProfileSummary::forCase($this)->all(),
        ];
    }

    public function toPublicArray(): array
    {
        $this->loadMissing(['category', 'reactions', 'sources', 'milestones', 'claims', 'outgoingLinks.toCase', 'incomingLinks.fromCase']);

        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'hook' => $this->hook,
            'summary' => $this->summary,
            'what_changes' => $this->what_changes,
            'who_affected' => $this->who_affected,
            'category' => $this->category?->toPublicArray(),
            'areas' => $this->areas ?? [],
            'change_types' => $this->change_types ?? [],
            'procedural_status' => $this->procedural_status,
            'public_status_label' => $this->public_status_label,
            'evidence_status' => $this->evidence_status,
            'observed_effect' => $this->observed_effect,
            'content_version' => $this->content_version,
            'sources_count' => (int) ($this->sources_count ?? 0),
            'pageviews' => (int) ($this->pageviews ?? 0),
            'before_text' => $this->before_text,
            'after_text' => $this->after_text,
            'detail' => $this->detail,
            'reactions' => $this->reactions->map->toPublicArray()->values(),
            'sources' => $this->sources->map->toPublicArray()->values(),
            'milestones' => $this->milestones->map->toPublicArray()->values(),
            'claims_count' => $this->claims->count(),
            'claims' => $this->claims->map->toPublicArray()->values(),
            'links' => $this->outgoingLinks
                ->map->toOutgoingPublicArray()
                ->filter(fn (array $link) => $link['slug'] !== '')
                ->values(),
            'linked_from' => $this->incomingLinks
                ->map->toIncomingPublicArray()
                ->filter(fn (array $link) => $link['slug'] !== '')
                ->values(),
            'profiles' => CaseProfileSummary::forCase($this)->all(),
            'published_at' => $this->published_at?->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'next_review_at' => $this->next_review_at?->toDateString(),
            'seo' => [
                'meta_title' => $this->meta_title ?? $this->title,
                'meta_description' => PlainText::excerpt($this->meta_description ?: $this->hook ?: $this->summary),
                'og_image_url' => $this->og_image_url,
                'robots' => 'index,follow',
                'canonical_url' => SiteUrl::of('/casos/'.$this->slug),
            ],
        ];
    }
}
