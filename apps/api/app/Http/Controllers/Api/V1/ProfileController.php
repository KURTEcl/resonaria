<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EditorialCase;
use App\Models\ProfileMention;
use App\Models\PublicProfile;
use App\Support\ProfilePublicActivity;
use App\Support\SiteUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PublicProfile::query()
            ->where('status', 'active')
            ->with(['currentRoles'])
            ->withCount([
                'mentions as mentions_count' => fn ($q) => $q->where('confidence', 'confirmed'),
            ]);

        if ($kind = $request->string('kind')->trim()->toString()) {
            $query->where('kind', $kind);
        }

        if ($level = $request->string('level')->trim()->toString()) {
            $query->whereHas('currentRoles', fn ($q) => $q->where('level', $level));
        }

        $profiles = $query->orderBy('display_name')->get()->map(function (PublicProfile $profile) {
            $row = $profile->toPublicListArray();
            $row['mentions_count'] = (int) $profile->mentions_count;

            return $row;
        });

        return response()->json(['data' => $profiles]);
    }

    public function show(string $slug): JsonResponse
    {
        $profile = PublicProfile::query()
            ->where('status', 'active')
            ->where('slug', $slug)
            ->with(['currentRoles', 'socialAccounts', 'aliases'])
            ->firstOrFail();

        $caseIds = ProfileMention::query()
            ->where('profile_id', $profile->id)
            ->where('confidence', 'confirmed')
            ->where('mentionable_type', EditorialCase::class)
            ->pluck('mentionable_id');

        $relatedCaseIds = ProfileMention::query()
            ->where('profile_id', $profile->id)
            ->where('confidence', 'confirmed')
            ->whereIn('mentionable_type', [
                \App\Models\CaseSource::class,
                \App\Models\CaseMilestone::class,
                \App\Models\Reaction::class,
            ])
            ->get()
            ->map(function (ProfileMention $mention) {
                $model = $mention->mentionable;
                if ($model instanceof \App\Models\CaseSource || $model instanceof \App\Models\CaseMilestone || $model instanceof \App\Models\Reaction) {
                    return $model->case_id ?? $model->editorialCase?->id;
                }

                return null;
            })
            ->filter()
            ->unique();

        $allCaseIds = $caseIds->merge($relatedCaseIds)->unique()->values();

        $topics = EditorialCase::query()
            ->published()
            ->whereIn('id', $allCaseIds)
            ->with(['category'])
            ->withCount(['reactions', 'claims'])
            ->orderByDesc('published_at')
            ->get()
            ->map->toPublicListArray()
            ->values();

        $payload = $profile->toPublicArray();
        $payload['topics'] = $topics;
        $payload['linked_sources'] = ProfilePublicActivity::linkedSources($profile)->all();
        $payload['linked_reactions'] = ProfilePublicActivity::linkedReactions($profile)->all();
        $payload['seo'] = [
            'canonical_url' => SiteUrl::of('/perfiles/'.$profile->slug),
            'meta_title' => $profile->display_name,
            'meta_description' => $profile->bio ?: 'Perfil público y temas relacionados en Resonaria.',
        ];

        return response()->json(['data' => $payload]);
    }
}
