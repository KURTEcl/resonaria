<?php

namespace App\Mcp\Tools;

use App\Models\EditorialCase;
use App\Support\TopicCoverOptimizer;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Http;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use RuntimeException;

#[Description('Sube la portada editorial de un tema publicado (cabecera, tarjeta y Open Graph). Usa image_base64 (PNG/JPEG/WebP) o image_url pública. Optimiza a WebP en /casos/{slug}/cover.webp. La imagen no debe llevar titular incrustado; ver docs de portada 16:9.')]
class SetTopicCover extends Tool
{
    protected string $name = 'set_topic_cover';

    private const MAX_BYTES = 20 * 1024 * 1024;

    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'slug' => ['required', 'string', 'max:180'],
            'image_base64' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url', 'max:2048'],
        ]);

        if (empty($data['image_base64']) && empty($data['image_url'])) {
            return Response::error('Indica image_base64 o image_url.');
        }

        if (! empty($data['image_base64']) && ! empty($data['image_url'])) {
            return Response::error('Usa solo image_base64 o image_url, no ambos.');
        }

        $case = EditorialCase::query()->published()->where('slug', $data['slug'])->first();

        if (! $case) {
            return Response::error('No hay un tema publicado con ese slug.');
        }

        try {
            $tempPath = ! empty($data['image_url'])
                ? $this->downloadToTemp((string) $data['image_url'])
                : $this->decodeBase64ToTemp((string) $data['image_base64']);

            $optimizer = app(TopicCoverOptimizer::class);
            $result = $optimizer->optimizeCase($case, $tempPath);

            $case->forceFill([
                'content_version' => (int) $case->content_version + 1,
                'reviewed_at' => now(),
            ])->save();
        } catch (RuntimeException $exception) {
            return Response::error($exception->getMessage());
        } finally {
            if (isset($tempPath) && is_file($tempPath)) {
                @unlink($tempPath);
            }
        }

        return Response::json([
            'slug' => $case->slug,
            'cover_image_url' => $result['public_url'],
            'og_image_url' => $result['public_url'],
            'bytes' => $result['bytes'],
            'width' => $result['width'],
            'height' => $result['height'],
            'public_path' => $result['public_url'],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()->description('Slug del tema publicado (mismo que list_topics / get_topic).')->required(),
            'image_base64' => $schema->string()->description('Imagen en base64 (PNG, JPEG o WebP). Puede incluir prefijo data:image/...;base64,. Máx. 20 MB decodificados.'),
            'image_url' => $schema->string()->description('URL HTTPS pública de la imagen si el agente no puede enviar base64. El servidor la descarga una vez.'),
        ];
    }

    private function decodeBase64ToTemp(string $payload): string
    {
        $payload = trim($payload);
        if (preg_match('/^data:image\/[a-z0-9+.-]+;base64,(.+)$/is', $payload, $matches)) {
            $payload = $matches[1];
        }

        $payload = preg_replace('/\s+/', '', $payload) ?? $payload;
        $binary = base64_decode($payload, true);

        if ($binary === false || $binary === '') {
            throw new RuntimeException('image_base64 no es base64 válido.');
        }

        if (strlen($binary) > self::MAX_BYTES) {
            throw new RuntimeException('La imagen supera el límite de 20 MB.');
        }

        return $this->writeTempBinary($binary);
    }

    private function downloadToTemp(string $url): string
    {
        $response = Http::timeout(60)
            ->withOptions(['allow_redirects' => ['max' => 3]])
            ->get($url);

        if (! $response->successful()) {
            throw new RuntimeException('No se pudo descargar image_url (HTTP '.$response->status().').');
        }

        $body = $response->body();
        if ($body === '' || strlen($body) > self::MAX_BYTES) {
            throw new RuntimeException('La imagen descargada está vacía o supera 20 MB.');
        }

        $contentType = strtolower((string) $response->header('Content-Type'));
        if ($contentType !== '' && ! str_contains($contentType, 'image/')) {
            throw new RuntimeException('image_url no devolvió un tipo image/*.');
        }

        return $this->writeTempBinary($body);
    }

    private function writeTempBinary(string $binary): string
    {
        $temp = tempnam(sys_get_temp_dir(), 'qp-cover-');
        if ($temp === false) {
            throw new RuntimeException('No se pudo crear archivo temporal.');
        }

        if (file_put_contents($temp, $binary) === false) {
            @unlink($temp);
            throw new RuntimeException('No se pudo escribir la imagen temporal.');
        }

        $info = @getimagesize($temp);
        if ($info === false) {
            @unlink($temp);
            throw new RuntimeException('El archivo no es una imagen válida (JPEG, PNG o WebP).');
        }

        return $temp;
    }
}
