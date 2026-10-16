<?php

namespace App\Mcp\Tools;

use App\Models\EditorialCase;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Devuelve un tema publicado, con su categoría, reacciones y notas relacionadas del portal.')]
#[IsReadOnly]
class GetTopic extends Tool
{
    protected string $name = 'get_topic';

    public function handle(Request $request): Response
    {
        $request->validate([
            'slug' => ['required', 'string'],
        ]);

        $case = EditorialCase::query()->published()->where('slug', $request->string('slug')->toString())->first();

        if (! $case) {
            return Response::error('No hay un tema publicado con ese slug.');
        }

        return Response::json($case->toPublicArray());
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()->description('Slug del tema.')->required(),
        ];
    }
}
