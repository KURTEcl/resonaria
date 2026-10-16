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

class CaseProfileSummary
{
    /**
     * @return Collection<int, array{slug: string, display_name: string, photo_url: ?string, role: string}>
     */
    public static function forCase(EditorialCase $case): Collection
    {
        $mentions = ProfileMention::query()
            ->where('confidence', 'confirmed')
            ->where(function ($query) use ($case) {
                $query->where(function ($inner) use ($case) {
                    $inner->where('mentionable_type', EditorialCase::class)
                        ->where('mentionable_id', $case->id);
                })->orWhere(function ($inner) use ($case) {
                    $inner->where('mentionable_type', CaseSource::class)
                        ->whereIn('mentionable_id', $case->sources()->pluck('id'));
                })->orWhere(function ($inner) use ($case) {
                    $inner->where('mentionable_type', CaseMilestone::class)
                        ->whereIn('mentionable_id', $case->milestones()->pluck('id'));
                })->orWhere(function ($inner) use ($case) {
                    $inner->where('mentionable_type', Reaction::class)
                        ->whereIn('mentionable_id', $case->reactions()->pluck('id'));
                })->orWhere(function ($inner) use ($case) {
                    $inner->where('mentionable_type', CaseClaim::class)
                        ->whereIn('mentionable_id', $case->claims()->pluck('id'));
                });
            })
            ->with('profile')
            ->get();

        return $mentions
            ->groupBy('profile_id')
            ->map(function (Collection $group) {
                $mention = $group->sortByDesc(fn (ProfileMention $m) => $m->role === 'protagonista')->first();
                $profile = $mention?->profile;
                if (! $profile instanceof PublicProfile || $profile->status !== 'active') {
                    return null;
                }

                return [
                    'slug' => $profile->slug,
                    'display_name' => $profile->display_name,
                    'photo_url' => $profile->photo_url,
                    'role' => $mention->role,
                ];
            })
            ->filter()
            ->values();
    }
}
