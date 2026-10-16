<?php

namespace App\Console\Commands;

use App\Services\SocialInspectDoctor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class SocialWhisperModel extends Command
{
    protected $signature = 'social:whisper-model {--force : Vuelve a bajar el archivo aunque ya exista}';

    protected $description = 'Descarga el modelo GGML de Whisper.cpp para transcribir en local';

    /**
     * @var list<string>
     */
    private array $allowed = ['tiny', 'base', 'small'];

    public function handle(SocialInspectDoctor $doctor): int
    {
        $name = strtolower((string) config('social.whisper_model', 'base'));
        if (! in_array($name, $this->allowed, true)) {
            throw ValidationException::withMessages([
                'model' => 'WHISPER_MODEL debe ser tiny, base o small.',
            ]);
        }

        $path = $doctor->whisperModelPath();
        if (is_readable($path) && filesize($path) > 1_000_000 && ! $this->option('force')) {
            $this->info('Ya está el modelo: '.$path);

            return self::SUCCESS;
        }

        File::ensureDirectoryExists(dirname($path));
        $url = 'https://huggingface.co/ggerganov/whisper.cpp/resolve/main/ggml-'.$name.'.bin';
        $this->comment('Descargando '.$url);

        $response = Http::sink($path)->timeout(300)->get($url);
        if ($response->failed() || ! is_readable($path) || filesize($path) < 1_000_000) {
            File::delete($path);
            $this->error('No se pudo descargar el modelo de Whisper.');

            return self::FAILURE;
        }

        $this->info('Listo: '.$path);

        return self::SUCCESS;
    }
}
