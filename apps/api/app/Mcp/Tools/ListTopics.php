<?php

namespace App\Mcp\Tools;

use App\Models\EditorialCase;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Lista temas publicados. Filtra por slug de categoría o por texto.')]
#[IsReadOnly]
class ListTopics extends Tool
{
    protected string $name = 'list_topics';

    public function handle(Request $request): Response
    {
        $query = EditorialCase::query()->published()->with('category')->orderByDesc('published_at');

        if ($category = $request->string('category_slug')->trim()->toString()) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $category));
        }

        if ($q = $request->string('q')->trim()->toString()) {
            $like = '%'.$q.'%';
            $query->where(function ($builder) use ($like) {
                $builder->where('title', 'like', $like)->orWhere('hook', 'like', $like);
            });
        }

        $topics = $query->limit(50)->get()->map(fn (EditorialCase $case) => [
            'slug' => $case->slug,
            'title' => $case->title,
            'hook' => $case->hook,
            'category' => $case->category?->slug,
        ])->values();

        return Response::json($topics);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'category_slug' => $schema->string()->description('Slug de categoría, opcional.'),
            'q' => $schema->string()->description('Texto a buscar en título o gancho, opcional.'),
        ];
    }
}
