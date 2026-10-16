<?php

namespace App\Mcp\Tools;

use App\Models\Category;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Lista las categorías globales en las que se puede publicar un tema.')]
#[IsReadOnly]
class ListCategories extends Tool
{
    protected string $name = 'list_categories';

    public function handle(Request $request): Response
    {
        $categories = Category::query()->orderBy('sort')->get()->map->toPublicArray()->values();

        return Response::json($categories);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
