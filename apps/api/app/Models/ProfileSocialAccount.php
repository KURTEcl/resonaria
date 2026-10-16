<?php

namespace App\Models;

use App\Support\ProfileText;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileSocialAccount extends Model
{
    protected $fillable = [
        'profile_id',
        'profile_role_id',
        'platform',
        'handle',
        'account_kind',
        'label',
        'started_on',
        'ended_on',
        'status',
        'use_for_avatar',
        'avatar_remote_url',
        'avatar_fetched_at',
    ];

    protected static function booted(): void
    {
        static::saving(function (ProfileSocialAccount $row) {
            $row->handle = ProfileText::normalizeHandle($row->handle);
            $row->platform = ProfileText::normalizePlatform($row->platform);
        });
    }

    protected function casts(): array
    {
        return [
            'started_on' => 'date',
            'ended_on' => 'date',
            'use_for_avatar' => 'boolean',
            'avatar_fetched_at' => 'datetime',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(PublicProfile::class, 'profile_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(ProfileRole::class, 'profile_role_id');
    }

    public function isActiveOn(?\Carbon\CarbonInterface $date = null): bool
    {
        $date = ($date ?? now())->startOfDay();

        if ($this->status === 'archived') {
            return false;
        }

        if ($this->started_on && $date->lt($this->started_on)) {
            return false;
        }

        if ($this->ended_on && $date->gt($this->ended_on)) {
            return false;
        }

        return true;
    }

    public function toPublicArray(): array
    {
        return [
            'platform' => $this->platform,
            'handle' => $this->handle,
            'account_kind' => $this->account_kind,
            'label' => $this->label,
        ];
    }

    public function toDeskArray(): array
    {
        return [
            'id' => $this->id,
            'profile_role_id' => $this->profile_role_id,
            'platform' => $this->platform,
            'handle' => $this->handle,
            'account_kind' => $this->account_kind,
            'label' => $this->label,
            'started_on' => $this->started_on?->toDateString(),
            'ended_on' => $this->ended_on?->toDateString(),
            'status' => $this->status,
            'use_for_avatar' => $this->use_for_avatar,
            'avatar_remote_url' => $this->avatar_remote_url,
            'avatar_fetched_at' => $this->avatar_fetched_at?->toIso8601String(),
        ];
    }
}
