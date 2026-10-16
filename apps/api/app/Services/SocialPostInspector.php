<?php

namespace App\Services;

use App\Support\SocialPostUrl;
use Illuminate\Http\Client\RequestException;
use Illuminate\Process\Exceptions\ProcessTimedOutException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RuntimeException;

class SocialPostInspector
{
    public function __construct(private SocialInspectDoctor $doctor) {}

    /**
     * @return array{
     *     platform: string,
     *     url: string,
     *     author: ?string,
     *     caption: ?string,
     *     duration_s: ?int,
     *     transcript: ?string,
     *     notes: list<string>
     * }
     */
    public function inspect(string $url, bool $transcribe = true): array
    {
        $parsed = SocialPostUrl::parse($url);
        $report = $this->doctor->report();

        if (! $report['yt_dlp']['ok']) {
            throw new RuntimeException($report['yt_dlp']['message']);
        }

        $meta = $this->metadata($parsed['url']);
        $notes = [];
        $transcript = null;

        if ($transcribe) {
            $backend = $this->doctor->transcriptionBackend($report);
            if (! $report['ffmpeg']['ok']) {
                $notes[] = $report['ffmpeg']['message'];
            } elseif ($backend === null) {
                $notes[] = $report['whisper']['ok']
                    ? $report['whisper_model']['message']
                    : $report['whisper']['message'];
            } elseif (($meta['duration_s'] ?? 0) > (int) config('social.max_seconds')) {
                $notes[] = 'El video supera el tope de '.config('social.max_seconds').' segundos; no se transcribe.';
            } else {
                $transcript = $this->transcribeAudio($parsed['url'], $notes, $backend);
            }
        }

        if (! $report['cookies_file']['ok']) {
            $notes[] = $report['cookies_file']['message'];
        }

        return [
            'platform' => $parsed['platform'],
            'url' => $parsed['url'],
            'author' => $meta['author'],
            'caption' => $meta['caption'],
            'duration_s' => $meta['duration_s'],
            'transcript' => $transcript,
            'notes' => $notes,
        ];
    }

    /**
     * @return array{author: ?string, caption: ?string, duration_s: ?int}
     */
    private function metadata(string $url): array
    {
        try {
            $result = Process::timeout(90)->run($this->ytdlpCommand(['--no-warnings', '--no-playlist', '-j', $url]));
        } catch (ProcessTimedOutException) {
            throw new RuntimeException('yt-dlp tardó demasiado en leer la publicación.');
        }
        if (! $result->successful()) {
            throw new RuntimeException($this->ytdlpError($result->errorOutput().$result->output()));
        }

        $data = json_decode($result->output(), true);
        if (! is_array($data)) {
            throw new RuntimeException('yt-dlp no devolvió metadata legible.');
        }

        $author = $data['uploader'] ?? $data['channel'] ?? $data['creator'] ?? $data['uploader_id'] ?? null;
        $caption = $data['description'] ?? $data['title'] ?? null;
        $duration = isset($data['duration']) ? (int) round((float) $data['duration']) : null;

        return [
            'author' => is_string($author) ? $author : null,
            'caption' => is_string($caption) ? $caption : null,
            'duration_s' => $duration,
        ];
    }

    /**
     * @param  list<string>  $notes
     */
    private function transcribeAudio(string $url, array &$notes, string $backend): ?string
    {
        $dir = storage_path('app/tmp/social-inspect/'.Str::uuid());
        File::ensureDirectoryExists($dir);

        try {
            $output = $dir.'/audio.%(ext)s';
            try {
                $result = Process::timeout(180)->run($this->ytdlpCommand([
                    '--no-warnings',
                    '--no-playlist',
                    '-x',
                    '--audio-format',
                    'mp3',
                    '--audio-quality',
                    '5',
                    '--max-filesize',
                    '25M',
                    '-o',
                    $output,
                    $url,
                ]));
            } catch (ProcessTimedOutException) {
                $notes[] = 'yt-dlp tardó demasiado en bajar el audio.';

                return null;
            }

            if (! $result->successful()) {
                $notes[] = $this->ytdlpError($result->errorOutput().$result->output());

                return null;
            }

            $audio = collect(File::files($dir))->first(
                fn ($file) => in_array($file->getExtension(), ['mp3', 'm4a', 'wav', 'ogg', 'webm'], true),
            );

            if (! $audio) {
                $notes[] = 'No hubo audio para transcribir.';

                return null;
            }

            if ($backend === 'local') {
                return $this->transcribeWithLocalWhisper($audio->getPathname(), $dir, $notes);
            }

            return $this->transcribeWithOpenAi($audio->getPathname(), $audio->getFilename(), $notes);
        } catch (RequestException $exception) {
            $notes[] = 'Whisper no pudo transcribir: '.$exception->getMessage();

            return null;
        } finally {
            File::deleteDirectory($dir);
        }
    }

    /**
     * @param  list<string>  $notes
     */
    private function transcribeWithLocalWhisper(string $audioPath, string $dir, array &$notes): ?string
    {
        $prefix = $dir.'/transcript';
        try {
            $result = Process::timeout(180)->run([
                (string) config('social.whisper_bin'),
                '-m',
                $this->doctor->whisperModelPath(),
                '-l',
                'es',
                '-f',
                $audioPath,
                '-otxt',
                '-of',
                $prefix,
                '-nt',
                '-np',
            ]);
        } catch (ProcessTimedOutException) {
            $notes[] = 'Whisper local tardó demasiado.';

            return null;
        }

        if (! $result->successful()) {
            $notes[] = 'Whisper local falló: '.Str::limit(trim($result->errorOutput().$result->output()), 240);

            return null;
        }

        $path = $prefix.'.txt';
        $text = File::exists($path) ? trim(File::get($path)) : trim($result->output());

        return $text !== '' ? $text : null;
    }

    /**
     * @param  list<string>  $notes
     */
    private function transcribeWithOpenAi(string $audioPath, string $filename, array &$notes): ?string
    {
        $response = Http::withToken((string) config('social.openai_key'))
            ->timeout(120)
            ->attach('file', File::get($audioPath), $filename, ['Content-Type' => 'audio/mpeg'])
            ->post('https://api.openai.com/v1/audio/transcriptions', [
                'model' => config('social.openai_whisper_model'),
                'language' => 'es',
                'response_format' => 'json',
            ]);

        if ($response->failed()) {
            $notes[] = 'Whisper en OpenAI no pudo transcribir: '.$response->status();

            return null;
        }

        $text = $response->json('text');

        return is_string($text) && $text !== '' ? $text : null;
    }

    /**
     * @param  list<string>  $args
     * @return list<string>
     */
    private function ytdlpCommand(array $args): array
    {
        $command = [(string) config('social.ytdlp_bin'), ...$args];
        $cookies = config('social.cookies_file');
        if (filled($cookies) && is_readable((string) $cookies)) {
            array_splice($command, 1, 0, ['--cookies', (string) $cookies]);
        }

        return $command;
    }

    private function ytdlpError(string $output): string
    {
        $text = trim($output);
        if ($text === '') {
            return 'yt-dlp no pudo leer la publicación.';
        }
        if (str_contains(strtolower($text), 'private') || str_contains(strtolower($text), 'login')) {
            return 'La publicación parece privada o pide inicio de sesión.';
        }

        return 'yt-dlp: '.Str::limit($text, 280);
    }
}
