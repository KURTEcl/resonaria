<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PublicProfile extends Model
{
    protected $fillable = [
        'slug',
        'display_name',
        'short_name',
        'kind',
        'status',
        'photo_url',
        'photo_source_account_id',
        'photo_updated_at',
        'bio',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'photo_updated_at' => 'datetime',
        ];
    }

    public function aliases(): HasMany
    {
        return $this->hasMany(ProfileAlias::class, 'profile_id');
    }

    public function roles(): HasMany
    {
        return $this->hasMany(ProfileRole::class, 'profile_id')->orderByDesc('started_on');
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(ProfileSocialAccount::class, 'profile_id');
    }

    public function mentions(): HasMany
    {
        return $this->hasMany(ProfileMention::class, 'profile_id');
    }

    public function photoSourceAccount(): BelongsTo
    {
        return $this->belongsTo(ProfileSocialAccount::class, 'photo_source_account_id');
    }

    public function currentRoles(): HasMany
    {
        return $this->roles()->whereNull('ended_on');
    }

    public function toPublicArray(): array
    {
        $this->loadMissing(['currentRoles', 'socialAccounts']);

        return [
            'slug' => $this->slug,
            'display_name' => $this->display_name,
            'short_name' => $this->short_name,
            'kind' => $this->kind,
            'photo_url' => $this->photo_url,
            'bio' => $this->bio,
            'roles' => $this->currentRoles->map->toPublicArray()->values(),
            'social_accounts' => $this->socialAccounts
                ->where('status', 'active')
                ->map->toPublicArray()
                ->values(),
        ];
    }

    public function toPublicListArray(): array
    {
        $this->loadMissing(['currentRoles']);

        return [
            'slug' => $this->slug,
            'display_name' => $this->display_name,
            'short_name' => $this->short_name,
            'kind' => $this->kind,
            'photo_url' => $this->photo_url,
            'current_role' => $this->currentRoles->first()?->title,
        ];
    }

    public function toDeskArray(): array
    {
        $this->loadMissing(['aliases', 'roles', 'socialAccounts', 'photoSourceAccount']);
        $confirmedMentions = $this->mentions()->where('confidence', 'confirmed')->count();
        $suggestedMentions = $this->mentions()->where('confidence', 'suggested')->count();

        return [
            'slug' => $this->slug,
            'display_name' => $this->display_name,
            'short_name' => $this->short_name,
            'kind' => $this->kind,
            'status' => $this->status,
            'photo_url' => $this->photo_url,
            'photo_updated_at' => $this->photo_updated_at?->toIso8601String(),
            'photo_source_account_id' => $this->photo_source_account_id,
            'bio' => $this->bio,
            'metadata' => $this->metadata ?? [],
            'aliases' => $this->aliases->map->toDeskArray()->values(),
            'roles' => $this->roles->map->toDeskArray()->values(),
            'social_accounts' => $this->socialAccounts->map->toDeskArray()->values(),
            'mentions_confirmed' => $confirmedMentions,
            'mentions_suggested' => $suggestedMentions,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
