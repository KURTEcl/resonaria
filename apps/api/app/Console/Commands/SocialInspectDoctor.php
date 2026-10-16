<?php

namespace App\Console\Commands;

use App\Services\SocialInspectDoctor as Doctor;
use Illuminate\Console\Command;

class SocialInspectDoctor extends Command
{
    protected $signature = 'social:inspect-doctor';

    protected $description = 'Revisa yt-dlp, ffmpeg y Whisper local para inspeccionar publicaciones de redes';

    public function handle(Doctor $doctor): int
    {
        $report = $doctor->report();
        $requiredOk = true;

        foreach ($report as $name => $item) {
            $label = $item['ok'] ? 'OK   ' : 'FALTA';
            $line = "{$label}  {$name}: {$item['message']}";
            $item['ok'] ? $this->info($line) : $this->error($line);

            if (in_array($name, ['yt_dlp', 'ffmpeg', 'whisper', 'whisper_model'], true) && ! $item['ok']) {
                $requiredOk = false;
            }
        }

        if (! $requiredOk) {
            $this->newLine();
            $this->comment('Instalación local (macOS): brew install yt-dlp ffmpeg whisper.cpp');
            $this->comment('Luego: php artisan social:whisper-model');

            return self::FAILURE;
        }

        $this->newLine();
        $this->comment('Transcripción: Whisper local (gratis). OPENAI_API_KEY es opcional como respaldo.');

        return self::SUCCESS;
    }
}
