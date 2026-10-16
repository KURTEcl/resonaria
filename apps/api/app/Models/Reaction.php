<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reaction extends Model
{
    protected $fillable = [
        'case_id',
        'platform',
        'url',
        'caption',
        'author_handle',
        'author_kind',
        'preview_image_url',
    ];

    public function editorialCase(): BelongsTo
    {
        return $this->belongsTo(EditorialCase::class, 'case_id');
    }

    public function toPublicArray(): array
    {
        return [
            'platform' => $this->platform,
            'url' => $this->url,
            'caption' => $this->caption,
            'author_handle' => $this->author_handle,
            'author_kind' => $this->author_kind ?: 'sin_clasificar',
            'preview_image_url' => $this->preview_image_url,
        ];
    }
}
