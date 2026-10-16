<?php

namespace App\Support;

class ProfileText
{
    public static function normalizeAlias(string $alias): string
    {
        $alias = mb_strtolower(trim($alias));
        $alias = preg_replace('/\s+/u', ' ', $alias) ?? $alias;

        return $alias;
    }

    public static function normalizeHandle(string $handle): string
    {
        $handle = trim($handle);
        $handle = ltrim($handle, '@');

        return mb_strtolower($handle);
    }

    public static function normalizePlatform(string $platform): string
    {
        $platform = mb_strtolower(trim($platform));

        return $platform === 'twitter' ? 'x' : $platform;
    }

    public static function wordBoundaryPattern(string $phrase): string
    {
        $escaped = preg_quote($phrase, '/');

        return '/(?<!\p{L})'.$escaped.'(?!\p{L})/iu';
    }
}
