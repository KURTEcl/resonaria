<?php

namespace App\Mcp\Tools;

use App\Models\EditorialCase;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Agrega una fuente de noticia o documento a un tema publicado. Úsala tras buscar prensa, DIPRES, leyes o reportajes que aún no estén en sources. No duplica la misma URL.')]
class AddSource extends Tool
{
    protected string $name = 'add_source';

    /** @var list<string> */
    public const KINDS = ['documento', 'prensa', 'noticia', 'ley', 'analisis'];

    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'slug' => ['required', 'string'],
            'title' => ['required', 'string', 'max:180'],
            'url' => ['required', 'url', 'max:2048'],
            'kind' => ['nullable', 'string', Rule::in(self::KINDS)],
            'locator' => ['nullable', 'string', 'max:240'],
            'published_on' => ['nullable', 'date'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $case = EditorialCase::query()->published()->where('slug', $data['slug'])->first();

        if (! $case) {
            return Response::error('No hay un tema publicado con ese slug.');
        }

        $existing = $case->sources()->where('url', $data['url'])->first();
        if ($existing) {
            return Response::json([
                'added' => false,
                'reason' => 'Esa URL ya está en las fuentes de este tema.',
                'topic' => $case->fresh()->toPublicArray(),
            ]);
        }

        $nextOrder = (int) $case->sources()->max('sort_order') + 1;
        $kind = $data['kind'] ?? 'prensa';
        $isPrimary = array_key_exists('is_primary', $data)
            ? (bool) $data['is_primary']
            : in_array($kind, ['documento', 'ley'], true);

        $case->sources()->create([
            'title' => $data['title'],
            'url' => $data['url'],
            'kind' => $kind,
            'locator' => $data['locator'] ?? null,
            'published_on' => $data['published_on'] ?? null,
            'is_primary' => $isPrimary,
            'sort_order' => $nextOrder,
        ]);

        $case->forceFill([
            'sources_count' => $case->sources()->count(),
            'content_version' => (int) $case->content_version + 1,
            'reviewed_at' => now(),
        ])->save();

        return Response::json([
            'added' => true,
            'topic' => $case->fresh()->toPublicArray(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()->description('Slug del tema publicado.')->required(),
            'title' => $schema->string()->description('Nombre de la fuente (medio + qué acredita).')->required(),
            'url' => $schema->string()->description('Enlace público al documento o noticia.')->required(),
            'kind' => $schema->string()->description('documento, prensa, noticia, ley o analisis. Por defecto prensa.'),
            'locator' => $schema->string()->description('Página, glosa, sección o partida que se cita.'),
            'published_on' => $schema->string()->description('Fecha de la pieza (YYYY-MM-DD), si se conoce.'),
            'is_primary' => $schema->boolean()->description('true para documento oficial o ley; false para prensa.'),
        ];
    }
}
