<?php

namespace App\Console\Commands;

use App\Models\EditorialCase;
use App\Support\TopicCoverOptimizer;
use Illuminate\Console\Command;

class RepairTopicCoverSpaFallback extends Command
{
    protected $signature = 'topics:repair-cover-spa-fallback
                            {--dry-run : Solo listar carpetas sin index.html}';

    protected $description = 'Añade index.html en /casos/{slug}/ para que las fichas SPA no devuelvan 403 tras subir portada';

    public function handle(TopicCoverOptimizer $optimizer): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $fixed = 0;

        $cases = EditorialCase::query()
            ->published()
            ->whereNotNull('og_image_url')
            ->where('og_image_url', 'like', '/casos/%')
            ->orderBy('slug')
            ->get(['id', 'slug', 'og_image_url']);

        foreach ($cases as $case) {
            $dir = $optimizer->slugDirectory($case->slug);
            $index = $dir.'/index.html';

            if (is_file($index) || is_link($index)) {
                continue;
            }

            if (! is_dir($dir)) {
                $this->warn("· {$case->slug}: sin carpeta en disco ({$dir})");

                continue;
            }

            if ($dryRun) {
                $this->line("· {$case->slug}: faltaría index.html");

                continue;
            }

            $optimizer->ensureSlugDirectoryServesSpa($case->slug);
            $this->line("· {$case->slug}: index.html listo");
            $fixed++;
        }

        if ($dryRun) {
            $this->info('Dry-run: no se escribió nada.');
        } else {
            $this->info("Listo. Carpetas reparadas: {$fixed}.");
        }

        return self::SUCCESS;
    }
}
