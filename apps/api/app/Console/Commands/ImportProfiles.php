<?php

namespace App\Console\Commands;

use App\Support\ProfileImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportProfiles extends Command
{
    protected $signature = 'profiles:import {path : Ruta al JSON de perfiles}';

    protected $description = 'Importa o actualiza perfiles públicos desde un archivo JSON';

    public function handle(ProfileImporter $importer): int
    {
        $path = $this->argument('path');
        if (! is_string($path) || ! File::exists($path)) {
            $this->error('No se encontró el archivo.');

            return self::FAILURE;
        }

        $decoded = json_decode(File::get($path), true);
        if (! is_array($decoded)) {
            $this->error('JSON inválido.');

            return self::FAILURE;
        }

        $rows = isset($decoded['profiles']) && is_array($decoded['profiles'])
            ? $decoded['profiles']
            : $decoded;

        $count = 0;
        foreach ($rows as $row) {
            if (! is_array($row) || empty($row['slug'])) {
                continue;
            }
            $importer->import($row);
            $count++;
            $this->line('· '.$row['slug']);
        }

        $this->info("Importados {$count} perfiles.");

        return self::SUCCESS;
    }
}
