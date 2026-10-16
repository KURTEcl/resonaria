<?php

namespace App\Support;

class SiteUrl
{
    public static function origin(): string
    {
        return rtrim((string) config('app.frontend_url', config('app.url')), '/');
    }

    public static function of(string $path = '/'): string
    {
        $path = '/'.ltrim($path, '/');
        if ($path === '/') {
            return self::origin();
        }

        return self::origin().rtrim($path, '/');
    }
}
