<?php

namespace App\Mcp\Tools;

use App\Services\SocialPostInspector;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use InvalidArgumentException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use RuntimeException;

#[Description('Lee una publicación pública de Instagram, TikTok o X. Extrae pie y autor; si transcribe, descarga audio temporal, usa Whisper local y borra el archivo. No guarda el video ni publica el tema. El resultado es para proponer mejoras: testimonio de redes no es hecho.')]
class InspectSocialPost extends Tool
{
    protected string $name = 'inspect_social_post';

    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'url' => ['required', 'url', 'max:2048'],
            'transcribe' => ['nullable', 'boolean'],
        ]);

        try {
            $result = app(SocialPostInspector::class)->inspect(
                $data['url'],
                array_key_exists('transcribe', $data) ? (bool) $data['transcribe'] : true,
            );
        } catch (InvalidArgumentException $exception) {
            return Response::error($exception->getMessage());
        } catch (RuntimeException $exception) {
            return Response::error($exception->getMessage());
        }

        return Response::json($result);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'url' => $schema->string()->description('Enlace público de Instagram, TikTok o X.')->required(),
            'transcribe' => $schema->boolean()->description('Si es true, descarga audio temporal y transcribe. Por defecto true.'),
        ];
    }
}
