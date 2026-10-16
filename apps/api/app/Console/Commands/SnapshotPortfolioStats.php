<?php

namespace App\Console\Commands;

use App\Models\PortfolioSnapshot;
use App\Services\PortfolioStatsService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SnapshotPortfolioStats extends Command
{
    protected $signature = 'stats:snapshot-portfolio {--date= : Fecha YYYY-MM-DD (por defecto hoy)}';

    protected $description = 'Guarda un snapshot diario de métricas de cartera';

    public function handle(PortfolioStatsService $stats): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))->startOfDay()
            : Carbon::today();

        $payload = $stats->build();

        PortfolioSnapshot::query()->updateOrCreate(
            ['snapshot_date' => $date->toDateString()],
            ['payload' => $payload],
        );

        $this->info('Snapshot guardado para '.$date->toDateString());

        return self::SUCCESS;
    }
}
