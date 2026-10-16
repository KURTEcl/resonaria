<?php

namespace App\Console\Commands;

use App\Support\ProfileMentionScanner;
use Illuminate\Console\Command;

class ScanPublishedProfiles extends Command
{
    protected $signature = 'profiles:scan-published {--dry-run : Solo muestra cuántas menciones crearía}';

    protected $description = 'Detecta menciones de perfiles en temas publicados y artefactos relacionados';

    public function handle(ProfileMentionScanner $scanner): int
    {
        $dryRun = (bool) $this->option('dry-run');
        if ($dryRun) {
            $this->warn('Modo dry-run: no se escribirá en la base de datos.');
        }

        $stats = $scanner->scanPublished($dryRun);

        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Nuevas menciones', $stats['created']],
                ['Confirmadas', $stats['confirmed']],
                ['Sugeridas', $stats['suggested']],
                ['Omitidas (ya existían)', $stats['skipped']],
            ],
        );

        return self::SUCCESS;
    }
}
