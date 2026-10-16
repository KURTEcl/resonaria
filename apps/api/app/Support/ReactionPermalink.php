<?php

namespace App\Support;

use InvalidArgumentException;

class ReactionPermalink
{
    public static function canonicalize(string $platform, string $url): string
    {
        $url = trim($url);

        return $platform === 'x' ? self::canonicalX($url) : $url;
    }

    public static function assertMatches(string $platform, string $url): void
    {
        if ($platform === 'reddit') {
            self::assertReddit($url);
        }

        if ($platform === 'threads') {
            self::assertThreads($url);
        }

        if ($platform === 'x') {
            self::canonicalX($url);
        }
    }

    private static function assertReddit(string $url): void
    {
        $parts = self::parts($url);
        if (! in_array($parts['host'], ['reddit.com', 'old.reddit.com'], true)) {
            throw new InvalidArgumentException('El enlace de Reddit tiene que ser un permalink de reddit.com.');
        }

        if (! preg_match('#^/r/[\w-]+/comments/[\w]+#', $parts['path'])) {
            throw new InvalidArgumentException('Usa el permalink del post: /r/comunidad/comments/id.');
        }
    }

    private static function canonicalX(string $url): string
    {
        $parts = self::parts($url);
        if (! in_array($parts['host'], ['x.com', 'twitter.com'], true)) {
            throw new InvalidArgumentException('Usa el permalink de X (x.com/usuario/status/id), no un enlace corto t.co.');
        }

        if (preg_match('#^/([^/]+)/status/(\d+)#', $parts['path'], $match) !== 1) {
            throw new InvalidArgumentException('Usa el permalink de la publicación: x.com/usuario/status/id.');
        }

        return 'https://x.com/'.$match[1].'/status/'.$match[2];
    }

    private static function assertThreads(string $url): void
    {
        $parts = self::parts($url);
        if (! in_array($parts['host'], ['threads.com', 'threads.net'], true)) {
            throw new InvalidArgumentException('El enlace de Threads tiene que ser un permalink de threads.com o threads.net.');
        }

        $path = $parts['path'];
        $post = preg_match('#^/@[^/]+/post/[^/]+#', $path) === 1;
        $short = preg_match('#^/t/[^/]+#', $path) === 1;
        if (! $post && ! $short) {
            throw new InvalidArgumentException('Usa el permalink del post de Threads (/@usuario/post/id o /t/id).');
        }
    }

    /**
     * @return array{host: string, path: string}
     */
    private static function parts(string $url): array
    {
        $parts = parse_url(trim($url));
        if (($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) {
            throw new InvalidArgumentException('Solo se aceptan enlaces HTTPS públicos.');
        }

        $host = preg_replace('/^www\./', '', strtolower((string) $parts['host']));

        return [
            'host' => $host,
            'path' => $parts['path'] ?? '/',
        ];
    }
}
