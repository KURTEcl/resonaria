<?php

namespace App\Support;

use App\Models\EditorialCase;
use Illuminate\Support\Facades\File;
use RuntimeException;

class TopicCoverOptimizer
{
    public const MAX_WIDTH = 1920;

    public const WEBP_QUALITY = 82;

    /** @var list<string> */
    private const SOURCE_BASENAMES = ['cover.jpg', 'cover.jpeg', 'cover.png', 'cover.webp'];

    public function webPublicRoot(): string
    {
        $configured = config('app.web_public_path');
        if (is_string($configured) && $configured !== '') {
            return rtrim($configured, '/');
        }

        $default = base_path('../web/public');
        $resolved = realpath($default);

        return $resolved !== false ? $resolved : $default;
    }

    public function slugDirectory(string $slug): string
    {
        return $this->webPublicRoot().'/casos/'.$slug;
    }

    public function publicUrl(string $slug): string
    {
        return '/casos/'.$slug.'/cover.webp';
    }

    public function destinationPath(string $slug): string
    {
        return $this->slugDirectory($slug).'/cover.webp';
    }

    /**
     * @return array{public_url: string, bytes: int, width: int, height: int}
     */
    public function optimizeFromPath(string $sourcePath, string $slug): array
    {
        $this->assertGdWebp();

        if (! is_file($sourcePath) || ! is_readable($sourcePath)) {
            throw new RuntimeException('No se pudo leer la imagen de origen.');
        }

        $directory = $this->slugDirectory($slug);
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $destination = $this->destinationPath($slug);
        $image = $this->loadImage($sourcePath);
        $image = $this->downscaleIfNeeded($image);
        $width = imagesx($image);
        $height = imagesy($image);

        $temp = $destination.'.'.uniqid('opt', true).'.webp';
        if (! imagewebp($image, $temp, self::WEBP_QUALITY)) {
            imagedestroy($image);
            @unlink($temp);
            throw new RuntimeException('No se pudo escribir cover.webp.');
        }

        imagedestroy($image);

        if (! rename($temp, $destination)) {
            @unlink($temp);
            throw new RuntimeException('No se pudo reemplazar cover.webp.');
        }

        $this->removeAlternateCovers($slug, $destination);
        $this->ensureSlugDirectoryServesSpa($slug);

        return [
            'public_url' => $this->publicUrl($slug),
            'bytes' => (int) filesize($destination),
            'width' => $width,
            'height' => $height,
        ];
    }

    /**
     * @return array{public_url: string, bytes: int, width: int, height: int}
     */
    public function optimizeCase(EditorialCase $case, ?string $sourcePath = null): array
    {
        $sourcePath ??= $this->resolveSourcePath($case->slug, $case->og_image_url);

        if ($sourcePath === null) {
            throw new RuntimeException('No hay imagen de portada para este tema.');
        }

        $result = $this->optimizeFromPath($sourcePath, $case->slug);

        $case->forceFill(['og_image_url' => $result['public_url']])->save();

        return $result;
    }

    public function resolveSourcePath(string $slug, ?string $ogImageUrl = null): ?string
    {
        $fromUrl = $this->absolutePathFromPublicUrl($ogImageUrl);
        if ($fromUrl !== null && is_file($fromUrl)) {
            return $fromUrl;
        }

        foreach (self::SOURCE_BASENAMES as $basename) {
            $candidate = $this->slugDirectory($slug).'/'.$basename;
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    public function absolutePathFromPublicUrl(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (! is_string($path) || $path === '') {
            $path = $url;
        }

        if (! str_starts_with($path, '/')) {
            return null;
        }

        return $this->webPublicRoot().$path;
    }

    /**
     * Nginx con `try_files $uri $uri/` sirve la carpeta /casos/{slug}/ como directorio
     * (403 sin autoindex) en lugar del fallback SPA. Un index que apunte a 200.html evita eso.
     */
    public function ensureSlugDirectoryServesSpa(string $slug): void
    {
        $directory = $this->slugDirectory($slug);
        if (! File::isDirectory($directory)) {
            return;
        }

        $index = $directory.'/index.html';
        if (is_file($index) || is_link($index)) {
            return;
        }

        $spaRoot = $this->webPublicRoot().'/200.html';
        if (! is_file($spaRoot)) {
            return;
        }

        if (! @symlink('../../200.html', $index)) {
            File::copy($spaRoot, $index);
        }
    }

    private function removeAlternateCovers(string $slug, string $keepPath): void
    {
        $directory = $this->slugDirectory($slug);
        if (! File::isDirectory($directory)) {
            return;
        }

        foreach (File::files($directory) as $file) {
            $path = $file->getPathname();
            if ($path === $keepPath) {
                continue;
            }

            $name = $file->getFilename();
            if (! preg_match('/^cover\.(jpe?g|png|webp)$/i', $name)) {
                continue;
            }

            File::delete($path);
        }
    }

    private function assertGdWebp(): void
    {
        if (! function_exists('imagewebp')) {
            throw new RuntimeException('PHP GD con soporte WebP es necesario (ext-gd).');
        }
    }

    /**
     * @return \GdImage|resource
     */
    private function loadImage(string $path)
    {
        $info = @getimagesize($path);
        if ($info === false) {
            throw new RuntimeException('El archivo no es una imagen válida.');
        }

        $type = $info[2] ?? 0;

        $image = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG => imagecreatefrompng($path),
            IMAGETYPE_WEBP => imagecreatefromwebp($path),
            default => false,
        };

        if ($image === false) {
            throw new RuntimeException('Formato de imagen no soportado (usa JPEG, PNG o WebP).');
        }

        if ($type === IMAGETYPE_PNG) {
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }

        return $image;
    }

    /**
     * @param  \GdImage|resource  $image
     * @return \GdImage|resource
     */
    private function downscaleIfNeeded($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= self::MAX_WIDTH) {
            return $image;
        }

        $newWidth = self::MAX_WIDTH;
        $newHeight = (int) max(1, round($height * ($newWidth / $width)));
        $resized = imagecreatetruecolor($newWidth, $newHeight);

        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
        imagealphablending($resized, true);

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);

        return $resized;
    }
}
