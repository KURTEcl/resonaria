<?php

namespace App\Services;

use App\Support\SocialPostUrl;
use Illuminate\Process\Exceptions\ProcessTimedOutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use InvalidArgumentException;
use RuntimeException;

class InstagramMediaResolver
{
    /**
     * @return array{
     *     kind: string,
     *     media_url: ?string,
     *     thumbnail_url: ?string,
     *     author: ?string,
     *     caption: ?string,
     *     permalink: string
     * }
     */
    public function resolve(string $url): array
    {
        $parsed = SocialPostUrl::parse($url);
        if ($parsed['platform'] !== 'instagram') {
            throw new InvalidArgumentException('Solo se resuelven enlaces de Instagram.');
        }

        $key = 'ig-media:'.sha1($parsed['url']);

        return Cache::remember($key, 8 * 60, fn () => $this->scrape($parsed['url']));
    }

    /**
     * @return array{
     *     kind: string,
     *     media_url: ?string,
     *     thumbnail_url: ?string,
     *     author: ?string,
     *     caption: ?string,
     *     permalink: string
     * }
     */
    private function scrape(string $url): array
    {
        $empty = [
            'kind' => 'image',
            'media_url' => null,
            'thumbnail_url' => null,
            'author' => null,
            'caption' => null,
            'permalink' => $url,
        ];

        try {
            $result = Process::timeout(45)->run([
                (string) config('social.ytdlp_bin'),
                '--no-warnings',
                '--no-playlist',
                '--no-download',
                '-j',
                $url,
            ]);
        } catch (ProcessTimedOutException) {
            throw new RuntimeException('No se pudo leer la publicación de Instagram a tiempo.');
        }

        if (! $result->successful()) {
            return $empty;
        }

        $data = json_decode($result->output(), true);
        if (! is_array($data)) {
            return $empty;
        }

        $mediaUrl = $this->pickUrl($data);
        $isVideo = ($data['ext'] ?? '') === 'mp4' || (is_string($mediaUrl) && preg_match('/\.(mp4|m3u8)(\?|$)/i', $mediaUrl) === 1);

        return [
            'kind' => $isVideo ? 'video' : 'image',
            'media_url' => $mediaUrl,
            'thumbnail_url' => is_string($data['thumbnail'] ?? null) ? $data['thumbnail'] : null,
            'author' => is_string($data['uploader'] ?? null) ? $data['uploader'] : null,
            'caption' => is_string($data['description'] ?? null) ? $data['description'] : null,
            'permalink' => $url,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function pickUrl(array $data): ?string
    {
        if (isset($data['url']) && is_string($data['url']) && str_starts_with($data['url'], 'http')) {
            return $data['url'];
        }

        $formats = is_array($data['formats'] ?? null) ? $data['formats'] : [];
        foreach ($formats as $format) {
            if (! is_array($format) || ! is_string($format['url'] ?? null)) {
                continue;
            }
            $vcodec = $format['vcodec'] ?? null;
            $acodec = $format['acodec'] ?? null;
            if (is_string($vcodec) && $vcodec !== 'none' && is_string($acodec) && $acodec !== 'none') {
                return $format['url'];
            }
        }
        foreach ($formats as $format) {
            if (! is_array($format) || ! is_string($format['url'] ?? null)) {
                continue;
            }
            if (($format['ext'] ?? null) === 'mp4' && empty($format['format_note'])) {
                return $format['url'];
            }
        }
        foreach (array_reverse($formats) as $format) {
            if (! is_array($format) || ! is_string($format['url'] ?? null)) {
                continue;
            }
            $vcodec = $format['vcodec'] ?? null;
            if (is_string($vcodec) && $vcodec !== 'none') {
                return $format['url'];
            }
        }

        return null;
    }
}
