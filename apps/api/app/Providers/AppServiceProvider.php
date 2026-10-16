<?php

namespace App\Providers;

use App\Models\EditorialCase;
use App\Support\SiteDiscovery;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        EditorialCase::saved(function () {
            $path = trim((string) config('app.discovery_public_path'));
            if ($path === '' || $path === '/') {
                return;
            }

            try {
                File::ensureDirectoryExists($path);
                foreach (SiteDiscovery::files() as $name => $contents) {
                    File::put(rtrim($path, '/').'/'.$name, $contents);
                }
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }
}
