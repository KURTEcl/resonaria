<?php

namespace App\Models;

use App\Support\ProfileText;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileAlias extends Model
{
    protected $fillable = [
        'profile_id',
        'alias',
        'alias_type',
    ];

    protected static function booted(): void
    {
        static::saving(function (ProfileAlias $row) {
            $row->alias = ProfileText::normalizeAlias($row->alias);
        });
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(PublicProfile::class, 'profile_id');
    }

    public function toDeskArray(): array
    {
        return [
            'id' => $this->id,
            'alias' => $this->alias,
            'alias_type' => $this->alias_type,
        ];
    }
}
