<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfileRole extends Model
{
    protected $fillable = [
        'profile_id',
        'title',
        'organization',
        'level',
        'started_on',
        'ended_on',
    ];

    protected function casts(): array
    {
        return [
            'started_on' => 'date',
            'ended_on' => 'date',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(PublicProfile::class, 'profile_id');
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(ProfileSocialAccount::class, 'profile_role_id');
    }

    public function toPublicArray(): array
    {
        return [
            'title' => $this->title,
            'organization' => $this->organization,
            'level' => $this->level,
            'started_on' => $this->started_on?->toDateString(),
            'ended_on' => $this->ended_on?->toDateString(),
        ];
    }

    public function toDeskArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'organization' => $this->organization,
            'level' => $this->level,
            'started_on' => $this->started_on?->toDateString(),
            'ended_on' => $this->ended_on?->toDateString(),
        ];
    }
}
