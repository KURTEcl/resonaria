<?php

namespace App\Support;

use App\Models\CaseClaim;
use App\Models\CaseMilestone;
use App\Models\CaseSource;
use App\Models\EditorialCase;
use App\Models\ProfileMention;
use App\Models\PublicProfile;
use App\Models\Reaction;
use Illuminate\Support\Collection;

class ProfilePublicActivity
{
    /**
     * @param  list<string>  $slugs
     * @return list<int>
     */
    public static function publishedCaseIdsForProfileSlugs(array $slugs): array
    {
        $slugs = array_values(array_filter(array_map('strval', $slugs)));
        if ($slugs === []) {
            return [];
        }

        $profileIds = PublicProfile::query()
            ->where('status', 'active')
            ->whereIn('slug', $slugs)
            ->pluck('id');

        if ($profileIds->isEmpty()) {
            return [];
        }

        return $profileIds
            ->flatMap(fn (int $profileId) => self::publishedCaseIdsForProfile($profileId))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, int>
     */
    public static function publishedCaseIdsForProfile(int $profileId): Collection
    {
        $direct = ProfileMention::query()
            ->where('profile_id', $profileId)
            ->where('confidence', 'confirmed')
            ->where('mentionable_type', EditorialCase::class)
            ->pluck('mentionable_id');

        $related = ProfileMention::query()
            ->where('profile_id', $profileId)
            ->where('confidence', 'confirmed')
            ->whereIn('mentionable_type', [
                CaseSource::class,
                CaseMilestone::class,
                Reaction::class,
                CaseClaim::class,
            ])
            ->get()
            ->map(function (ProfileMention $mention) {
                $model = $mention->mentionable;
                if ($model instanceof CaseSource || $model instanceof CaseMilestone || $model instanceof Reaction) {
                    return $model->case_id ?? $model->editorialCase?->id;
                }
                if ($model instanceof CaseClaim) {
                    return $model->case_id;
                }

                return null;
            })
            ->filter();

        return $direct->merge($related)->unique()->values();
    }

    /**
     * @return Collection<int, array{title: string, url: string, kind: string, published_on: ?string, case_slug: ?string, case_title: ?string}>
     */
    public static function linkedSources(PublicProfile $profile, int $limit = 12): Collection
    {
        return ProfileMention::query()
            ->where('profile_id', $profile->id)
            ->where('confidence', 'confirmed')
            ->where('mentionable_type', CaseSource::class)
            ->with(['mentionable.case'])
            ->latest('updated_at')
            ->limit($limit * 2)
            ->get()
            ->map(function (ProfileMention $mention) {
                $source = $mention->mentionable;
                if (! $source instanceof CaseSource || ! $source->case instanceof EditorialCase) {
                    return null;
                }
                if ($source->case->publication_status !== 'published') {
                    return null;
                }

                return [
                    'title' => $source->title,
                    'url' => $source->url,
                    'kind' => $source->kind,
                    'published_on' => $source->published_on?->toDateString(),
                    'case_slug' => $source->case->slug,
                    'case_title' => $source->case->title,
                ];
            })
            ->filter()
            ->unique('url')
            ->take($limit)
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public static function linkedReactions(PublicProfile $profile, int $limit = 12): Collection
    {
        return ProfileMention::query()
            ->where('profile_id', $profile->id)
            ->where('confidence', 'confirmed')
            ->where('mentionable_type', Reaction::class)
            ->with(['mentionable.editorialCase'])
            ->latest('updated_at')
            ->limit($limit * 2)
            ->get()
            ->map(function (ProfileMention $mention) {
                $reaction = $mention->mentionable;
                if (! $reaction instanceof Reaction || ! $reaction->editorialCase instanceof EditorialCase) {
                    return null;
                }
                if ($reaction->editorialCase->publication_status !== 'published') {
                    return null;
                }

                $row = $reaction->toPublicArray();
                $row['case_slug'] = $reaction->editorialCase->slug;
                $row['case_title'] = $reaction->editorialCase->title;

                return $row;
            })
            ->filter()
            ->unique('url')
            ->take($limit)
            ->values();
    }
}
