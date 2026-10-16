<?php

namespace App\Mcp\Tools;

use App\Models\EditorialCase;
use App\Support\ReactionAuthorKinds;
use App\Support\ReactionPermalink;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Agrega una reacción de Instagram, TikTok, X, Threads o Reddit a un tema publicado. Solo guarda el enlace, no el video.')]
class AddReaction extends Tool
{
    protected string $name = 'add_reaction';

    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'slug' => ['required', 'string'],
            'platform' => ['required', 'string', Rule::in(['instagram', 'tiktok', 'x', 'threads', 'reddit'])],
            'url' => ['required', 'url', 'max:2048'],
            'caption' => ['nullable', 'string', 'max:500'],
            'author_handle' => ['nullable', 'string', 'max:80'],
            'author_kind' => ['nullable', 'string', Rule::in(ReactionAuthorKinds::keys())],
        ]);

        $case = EditorialCase::query()->published()->where('slug', $data['slug'])->first();

        if (! $case) {
            return Response::error('No hay un tema publicado con ese slug.');
        }

        try {
            $data['url'] = ReactionPermalink::canonicalize($data['platform'], $data['url']);
            ReactionPermalink::assertMatches($data['platform'], $data['url']);
        } catch (InvalidArgumentException $exception) {
            return Response::error($exception->getMessage());
        }

        if ($case->reactions()->where('url', $data['url'])->exists()) {
            return Response::json([
                'added' => false,
                'reason' => 'Esa URL ya está en las reacciones de este tema.',
                'topic' => $case->fresh()->toPublicArray(),
            ]);
        }

        $case->reactions()->create([
            'platform' => $data['platform'],
            'url' => $data['url'],
            'caption' => $data['caption'] ?? null,
            'author_handle' => $data['author_handle'] ?? null,
            'author_kind' => $data['author_kind'] ?? 'sin_clasificar',
        ]);

        return Response::json($case->fresh()->toPublicArray());
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()->description('Slug del tema.')->required(),
            'platform' => $schema->string()->description('instagram, tiktok, x, threads o reddit.')->required(),
            'url' => $schema->string()->description('Enlace público a la publicación.')->required(),
            'caption' => $schema->string()->description('Pie corto de por qué se seleccionó.'),
            'author_handle' => $schema->string()->description('Usuario público, si corresponde.'),
            'author_kind' => $schema->string()->description(
                'Tipo de cuenta: politico, periodista, persona_natural, medio_oficial, medio_comunicacion, organizacion, sin_clasificar.',
            ),
        ];
    }
}
