<?php

namespace App\Mcp\Tools;

use App\Models\PublicProfile;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Lista perfiles activos (Estado, Congreso, gobernadores, partidos, líderes, prensa, personas de interés) con slug y cargo. Devuelve kind_options y creation_guide para add_profile_mention.')]
class ListProfiles extends Tool
{
    protected string $name = 'list_profiles';

    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $query = PublicProfile::query()
            ->where('status', 'active')
            ->with(['currentRoles'])
            ->orderBy('display_name');

        if (! empty($data['q'])) {
            $like = '%'.mb_strtolower($data['q']).'%';
            $query->where(function ($inner) use ($like) {
                $inner->whereRaw('LOWER(display_name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(slug) LIKE ?', [$like])
                    ->orWhereHas('aliases', fn ($q) => $q->whereRaw('LOWER(alias) LIKE ?', [$like]));
            });
        }

        $profiles = $query->limit(50)->get()->map(fn (PublicProfile $profile) => [
            'slug' => $profile->slug,
            'display_name' => $profile->display_name,
            'current_role' => $profile->currentRoles->first()?->title,
            'kind' => $profile->kind,
        ]);

        return Response::json([
            'profiles' => $profiles,
            'kind_options' => config('profiles.kinds', []),
            'creation_guide' => trim(config('profiles.agent_creation_guide', '')),
        ]);
    }
}
