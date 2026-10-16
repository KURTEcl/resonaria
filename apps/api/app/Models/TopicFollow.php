<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopicFollow extends Model
{
    protected $fillable = [
        'case_id',
        'follower_key',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(EditorialCase::class, 'case_id');
    }
}
