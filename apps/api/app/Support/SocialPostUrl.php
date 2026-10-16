<?php

namespace App\Support;

use InvalidArgumentException;

class SocialPostUrl
{
    /**
     * @return array{platform: string, url: string, host: string, instagram: ?array{type: string, id: string, is_share: bool}}
     */
    public static function parse(string $raw): array
    {
        $url = trim($raw);
        $parts = parse_url($url);

        if (($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) {
            throw new InvalidArgumentException('Solo se aceptan enlaces HTTPS públicos.');
        }

        $host = preg_replace('/^www\./', '', strtolower((string) $parts['host']));
        $path = $parts['path'] ?? '/';

        $platform = match (true) {
            in_array($host, ['instagram.com', 'instagr.am'], true) => 'instagram',
            str_ends_with($host, 'tiktok.com') => 'tiktok',
            in_array($host, ['x.com', 'twitter.com'], true) => 'x',
            default => null,
        };

        if (! $platform) {
            throw new InvalidArgumentException('Dominio no permitido. Usa Instagram, TikTok o X.');
        }

        $instagram = $platform === 'instagram' ? self::parseInstagramPath($path) : null;
        if ($platform === 'instagram' && $instagram === null) {
            throw new InvalidArgumentException('No se reconoce el enlace de Instagram.');
        }

        return [
            'platform' => $platform,
            'url' => $url,
            'host' => $host,
            'instagram' => $instagram,
        ];
    }

    /**
     * @return array{type: string, id: string, is_share: bool}|null
     */
    public static function parseInstagramPath(string $path): ?array
    {
        $parts = array_values(array_filter(explode('/', $path), fn (string $segment) => $segment !== ''));
        if ($parts === []) {
            return null;
        }

        if ($parts[0] === 'share') {
            $typed = isset($parts[1]) ? self::asPostType($parts[1]) : null;
            if ($typed && isset($parts[2])) {
                return ['type' => $typed, 'id' => $parts[2], 'is_share' => true];
            }
            if (isset($parts[1])) {
                return ['type' => 'reel', 'id' => $parts[1], 'is_share' => true];
            }

            return null;
        }

        $direct = self::asPostType($parts[0]);
        if ($direct && isset($parts[1])) {
            return ['type' => $direct, 'id' => $parts[1], 'is_share' => false];
        }

        $nested = isset($parts[1]) ? self::asPostType($parts[1]) : null;
        if ($nested && isset($parts[2])) {
            return ['type' => $nested, 'id' => $parts[2], 'is_share' => false];
        }

        return null;
    }

    private static function asPostType(string $value): ?string
    {
        if ($value === 'reels') {
            return 'reel';
        }

        return in_array($value, ['p', 'reel', 'tv'], true) ? $value : null;
    }
}
