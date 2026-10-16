<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProfileAlias;
use App\Models\ProfileMention;
use App\Models\ProfileRole;
use App\Models\ProfileSocialAccount;
use App\Models\PublicProfile;
use App\Support\ProfileAvatarService;
use App\Support\ProfileText;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeskProfileController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->requireDesk($request);

        $profiles = PublicProfile::query()
            ->with(['currentRoles'])
            ->withCount([
                'mentions as mentions_confirmed_count' => fn ($q) => $q->where('confidence', 'confirmed'),
                'mentions as mentions_suggested_count' => fn ($q) => $q->where('confidence', 'suggested'),
            ])
            ->orderBy('display_name')
            ->get()
            ->map(fn (PublicProfile $profile) => [
                'slug' => $profile->slug,
                'display_name' => $profile->display_name,
                'kind' => $profile->kind,
                'status' => $profile->status,
                'photo_url' => $profile->photo_url,
                'current_role' => $profile->currentRoles->first()?->title,
                'mentions_confirmed' => (int) $profile->mentions_confirmed_count,
                'mentions_suggested' => (int) $profile->mentions_suggested_count,
            ]);

        return response()->json(['data' => $profiles]);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $this->requireDesk($request);

        $profile = PublicProfile::query()->where('slug', $slug)->firstOrFail();

        return response()->json(['data' => $profile->toDeskArray()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->requireDesk($request);

        $data = $request->validate([
            'slug' => ['required', 'string', 'max:128', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:public_profiles,slug'],
            'display_name' => ['required', 'string', 'max:180'],
            'short_name' => ['nullable', 'string', 'max:80'],
            'kind' => ['nullable', Rule::in(array_keys(config('profiles.kinds', [])))],
            'status' => ['nullable', Rule::in(['active', 'archived'])],
            'bio' => ['nullable', 'string', 'max:2000'],
        ]);

        $profile = PublicProfile::query()->create([
            'slug' => $data['slug'],
            'display_name' => $data['display_name'],
            'short_name' => $data['short_name'] ?? null,
            'kind' => $data['kind'] ?? 'otro',
            'status' => $data['status'] ?? 'active',
            'bio' => $data['bio'] ?? null,
        ]);

        return response()->json(['data' => $profile->fresh()->toDeskArray()], 201);
    }

    public function update(Request $request, string $slug): JsonResponse
    {
        $this->requireDesk($request);

        $profile = PublicProfile::query()->where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'display_name' => ['sometimes', 'string', 'max:180'],
            'short_name' => ['sometimes', 'nullable', 'string', 'max:80'],
            'kind' => ['sometimes', Rule::in(array_keys(config('profiles.kinds', [])))],
            'status' => ['sometimes', Rule::in(['active', 'archived'])],
            'bio' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'photo_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
        ]);

        $profile->fill($data);
        $profile->save();

        return response()->json(['data' => $profile->fresh()->toDeskArray()]);
    }

    public function refreshAvatar(Request $request, string $slug, ProfileAvatarService $avatars): JsonResponse
    {
        $this->requireDesk($request);

        $profile = PublicProfile::query()->where('slug', $slug)->firstOrFail();
        $ok = $avatars->refresh($profile);

        if (! $ok) {
            return response()->json(['message' => 'No se pudo actualizar la foto desde X.'], 422);
        }

        return response()->json(['data' => $profile->fresh()->toDeskArray()]);
    }

    public function storeAlias(Request $request, string $slug): JsonResponse
    {
        $this->requireDesk($request);

        $profile = PublicProfile::query()->where('slug', $slug)->firstOrFail();
        $data = $request->validate([
            'alias' => ['required', 'string', 'max:240'],
            'alias_type' => ['nullable', Rule::in(array_keys(config('profiles.alias_types', [])))],
        ]);

        $alias = ProfileAlias::query()->create([
            'profile_id' => $profile->id,
            'alias' => ProfileText::normalizeAlias($data['alias']),
            'alias_type' => $data['alias_type'] ?? 'nombre_completo',
        ]);

        return response()->json(['data' => $alias->toDeskArray()], 201);
    }

    public function destroyAlias(Request $request, string $slug, int $aliasId): JsonResponse
    {
        $this->requireDesk($request);

        $profile = PublicProfile::query()->where('slug', $slug)->firstOrFail();
        ProfileAlias::query()->where('profile_id', $profile->id)->where('id', $aliasId)->delete();

        return response()->json(['ok' => true]);
    }

    public function storeRole(Request $request, string $slug): JsonResponse
    {
        $this->requireDesk($request);

        $profile = PublicProfile::query()->where('slug', $slug)->firstOrFail();
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'organization' => ['nullable', 'string', 'max:180'],
            'level' => ['nullable', Rule::in(array_keys(config('profiles.role_levels', [])))],
            'started_on' => ['nullable', 'date'],
            'ended_on' => ['nullable', 'date'],
        ]);

        $role = ProfileRole::query()->create([
            'profile_id' => $profile->id,
            ...$data,
            'level' => $data['level'] ?? 'otro',
        ]);

        return response()->json(['data' => $role->toDeskArray()], 201);
    }

    public function storeSocialAccount(Request $request, string $slug): JsonResponse
    {
        $this->requireDesk($request);

        $profile = PublicProfile::query()->where('slug', $slug)->firstOrFail();
        $data = $request->validate([
            'platform' => ['required', 'string', 'max:16'],
            'handle' => ['required', 'string', 'max:80'],
            'account_kind' => ['nullable', Rule::in(array_keys(config('profiles.account_kinds', [])))],
            'label' => ['nullable', 'string', 'max:240'],
            'profile_role_id' => ['nullable', 'integer', 'exists:profile_roles,id'],
            'started_on' => ['nullable', 'date'],
            'ended_on' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in(['active', 'archived'])],
            'use_for_avatar' => ['nullable', 'boolean'],
        ]);

        $account = ProfileSocialAccount::query()->create([
            'profile_id' => $profile->id,
            'platform' => ProfileText::normalizePlatform($data['platform']),
            'handle' => ProfileText::normalizeHandle($data['handle']),
            'account_kind' => $data['account_kind'] ?? 'personal',
            'label' => $data['label'] ?? null,
            'profile_role_id' => $data['profile_role_id'] ?? null,
            'started_on' => $data['started_on'] ?? null,
            'ended_on' => $data['ended_on'] ?? null,
            'status' => $data['status'] ?? 'active',
            'use_for_avatar' => (bool) ($data['use_for_avatar'] ?? false),
        ]);

        return response()->json(['data' => $account->toDeskArray()], 201);
    }

    public function mentions(Request $request): JsonResponse
    {
        $this->requireDesk($request);

        $query = ProfileMention::query()->with('profile')->orderByDesc('updated_at');

        if ($request->boolean('suggested')) {
            $query->where('confidence', 'suggested');
        }

        $mentions = $query->limit(200)->get()->map->toDeskArray();

        return response()->json(['data' => $mentions]);
    }

    public function storeMention(Request $request): JsonResponse
    {
        $this->requireDesk($request);

        $data = $request->validate([
            'profile_slug' => ['required', 'string'],
            'mentionable_type' => ['required', 'string', Rule::in(['EditorialCase', 'CaseSource', 'CaseMilestone', 'Reaction', 'CaseClaim'])],
            'mentionable_id' => ['required', 'integer'],
            'role' => ['nullable', Rule::in(array_keys(config('profiles.mention_roles', [])))],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'context_note' => ['nullable', 'string', 'max:240'],
        ]);

        $profile = PublicProfile::query()->where('slug', $data['profile_slug'])->firstOrFail();
        $class = 'App\\Models\\'.$data['mentionable_type'];

        $mention = ProfileMention::query()->create([
            'profile_id' => $profile->id,
            'mentionable_type' => $class,
            'mentionable_id' => $data['mentionable_id'],
            'role' => $data['role'] ?? 'mencionado',
            'origin' => 'manual',
            'confidence' => 'confirmed',
            'excerpt' => $data['excerpt'] ?? null,
            'context_note' => $data['context_note'] ?? null,
        ]);

        return response()->json(['data' => $mention->toDeskArray()], 201);
    }

    public function updateMention(Request $request, int $id): JsonResponse
    {
        $this->requireDesk($request);

        $mention = ProfileMention::query()->findOrFail($id);

        $data = $request->validate([
            'role' => ['sometimes', Rule::in(array_keys(config('profiles.mention_roles', [])))],
            'confidence' => ['sometimes', Rule::in(['confirmed', 'suggested'])],
            'context_note' => ['sometimes', 'nullable', 'string', 'max:240'],
        ]);

        $mention->fill($data);
        $mention->save();

        return response()->json(['data' => $mention->fresh()->toDeskArray()]);
    }

    public function destroyMention(Request $request, int $id): JsonResponse
    {
        $this->requireDesk($request);

        ProfileMention::query()->where('id', $id)->delete();

        return response()->json(['ok' => true]);
    }

    private function requireDesk(Request $request): void
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();
        $abilities = $token?->abilities ?? [];

        if (! $user || ! $token || ! in_array('desk', $abilities, true)) {
            abort(401);
        }
    }
}
