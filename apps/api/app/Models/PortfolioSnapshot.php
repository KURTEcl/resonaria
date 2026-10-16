<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortfolioSnapshot extends Model
{
    protected $fillable = [
        'snapshot_date',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_date' => 'date',
            'payload' => 'array',
        ];
    }
}
