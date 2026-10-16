<?php

namespace App\Console\Commands;

use App\Models\EditorialCase;
use App\Support\TopicCoverOptimizer;
use Illuminate\Console\Command;
use RuntimeException;

class OptimizeTopicCover extends Command
{
    protected $signature = 'topics:optimize-cover
                            {slug? : Slug del tema}
                            {--all : Procesar todos los temas con portada en disco o en og_image_url}
                            {--dry-run : Solo listar qué se optimizaría}';

    protected $description = 'Convierte la portada del tema a cover.webp optimizado y actualiza og_image_url';

    public function handle(TopicCoverOptimizer $optimizer): int
    {
        $slug = $this->argument('slug');
        $all = (bool) $this->option('all');
        $dryRun = (bool) $this->option('dry-run');

        if ($slug && $all) {
            $this->error('Usa slug o --all, no ambos.');

            return self::FAILURE;
        }

        if (! $slug && ! $all) {
            $this->error('Indica un slug o usa --all.');

            return self::FAILURE;
        }

        $cases = $slug
            ? EditorialCase::query()->where('slug', $slug)->get()
            : EditorialCase::query()->orderBy('slug')->get();

        if ($cases->isEmpty()) {
            $this->warn('No hay temas que procesar.');

            return self::SUCCESS;
        }

        $done = 0;
        $skipped = 0;

        foreach ($cases as $case) {
            $source = $optimizer->resolveSourcePath($case->slug, $case->og_image_url);
            if ($source === null) {
                if ($slug) {
                    $this->warn('Sin archivo de portada para '.$case->slug);
                }
                $skipped++;

                continue;
            }

            $dest = $optimizer->destinationPath($case->slug);
            if ($source === $dest && ! $dryRun) {
                $this->line('· '.$case->slug.' (ya es cover.webp)');
                $skipped++;

                continue;
            }

            if ($dryRun) {
                $this->line('→ '.$case->slug.' ← '.$source);

                continue;
            }

            try {
                $result = $optimizer->optimizeCase($case, $source);
                $kb = number_format($result['bytes'] / 1024, 1);
                $this->line("✓ {$case->slug} → {$result['public_url']} ({$result['width']}×{$result['height']}, {$kb} KB)");
                $done++;
            } catch (RuntimeException $exception) {
                $this->error("✗ {$case->slug}: {$exception->getMessage()}");
                $skipped++;
            }
        }

        if ($dryRun) {
            $this->info('Dry-run terminado.');

            return self::SUCCESS;
        }

        $this->info("Optimizadas: {$done}. Omitidas o error: {$skipped}.");

        return self::SUCCESS;
    }
}
