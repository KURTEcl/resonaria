<?php

namespace App\Console\Commands;

use App\Support\SiteDiscovery;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishDiscoveryFiles extends Command
{
    protected $signature = 'discovery:publish {path : Directorio web público (htdocs)}';

    protected $description = 'Escribe sitemap.xml, robots.txt y llms.txt en el directorio web';

    public function handle(): int
    {
        $path = rtrim((string) $this->argument('path'), '/');
        if ($path === '' || $path === '/') {
            $this->error('Ruta inválida.');

            return self::FAILURE;
        }

        File::ensureDirectoryExists($path);

        foreach (SiteDiscovery::files() as $name => $contents) {
            File::put($path.'/'.$name, $contents);
            $this->line($name);
        }

        return self::SUCCESS;
    }
}
