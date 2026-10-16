<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseAnalyticsDaily extends Model
{
    protected $table = 'case_analytics_daily';

    protected $fillable = [
        'case_id',
        'metric_date',
        'views',
        'source_clicks',
    ];

    protected function casts(): array
    {
        return [
            'metric_date' => 'date',
        ];
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(EditorialCase::class, 'case_id');
    }
}
