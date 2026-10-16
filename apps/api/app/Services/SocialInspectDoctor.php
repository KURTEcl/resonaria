<?php

namespace App\Services;

use Illuminate\Process\Exceptions\ProcessTimedOutException;
use Illuminate\Support\Facades\Process;

class SocialInspectDoctor
{
    /**
     * @return array<string, array{ok: bool, message: string, bin?: string}>
     */
    public function report(): array
    {
        $cookies = config('social.cookies_file');

        return [
            'yt_dlp' => $this->binary(
                (string) config('social.ytdlp_bin'),
                'yt-dlp no está instalado. En macOS: brew install yt-dlp',
            ),
            'ffmpeg' => $this->binary(
                (string) config('social.ffmpeg_bin'),
                'ffmpeg no está instalado. En macOS: brew install ffmpeg',
            ),
            'whisper' => $this->binary(
                (string) config('social.whisper_bin'),
                'whisper-cli no está instalado. En macOS: brew install whisper.cpp',
            ),
            'whisper_model' => $this->whisperModel(),
            'openai_key' => filled(config('social.openai_key'))
                ? ['ok' => true, 'message' => 'OPENAI_API_KEY presente (respaldo opcional)']
                : ['ok' => true, 'message' => 'sin OPENAI_API_KEY (se usa Whisper local)'],
            'cookies_file' => $this->cookies($cookies),
        ];
    }

    public function whisperModelPath(): string
    {
        $custom = config('social.whisper_model_path');
        if (filled($custom)) {
            return (string) $custom;
        }

        $name = preg_replace('/[^a-z0-9.-]/', '', strtolower((string) config('social.whisper_model', 'base'))) ?: 'base';

        return storage_path('app/whisper/ggml-'.$name.'.bin');
    }

    /**
     * @param  array<string, array{ok: bool, message: string, bin?: string}>|null  $report
     */
    public function transcriptionBackend(?array $report = null): ?string
    {
        $report ??= $this->report();
        if ($report['whisper']['ok'] && $report['whisper_model']['ok']) {
            return 'local';
        }
        if (filled(config('social.openai_key'))) {
            return 'openai';
        }

        return null;
    }

    /**
     * @return array{ok: bool, message: string, bin: string}
     */
    private function binary(string $bin, string $missing): array
    {
        try {
            $result = null;
            foreach (['-version', '--version'] as $flag) {
                $result = Process::timeout(30)->run([$bin, $flag]);
                if ($result->successful()) {
                    break;
                }
            }
        } catch (ProcessTimedOutException) {
            return ['ok' => false, 'message' => $missing.' (tiempo de espera agotado)', 'bin' => $bin];
        }

        if ($result?->successful()) {
            $first = trim(strtok($result->output(), "\n") ?: $bin);

            return ['ok' => true, 'message' => $first, 'bin' => $bin];
        }

        return ['ok' => false, 'message' => $missing, 'bin' => $bin];
    }

    /**
     * @return array{ok: bool, message: string}
     */
    private function whisperModel(): array
    {
        $path = $this->whisperModelPath();
        if (is_readable($path) && filesize($path) > 1024) {
            return ['ok' => true, 'message' => $path];
        }

        return [
            'ok' => false,
            'message' => 'Falta el modelo GGML. En local: php artisan social:whisper-model',
        ];
    }

    /**
     * @return array{ok: bool, message: string}
     */
    private function cookies(mixed $path): array
    {
        if (! filled($path)) {
            return ['ok' => true, 'message' => 'sin cookies (opcional)'];
        }

        if (is_readable((string) $path)) {
            return ['ok' => true, 'message' => 'cookies en '.$path];
        }

        return ['ok' => false, 'message' => 'YTDLP_COOKIES_FILE no se puede leer: '.$path];
    }
}
