<?php

namespace App\Mcp\Tools;

use App\Models\Category;
use App\Models\EditorialCase;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Publica un tema nuevo dentro de una categoría existente. El slug se genera desde el título.')]
class CreateTopic extends Tool
{
    protected string $name = 'create_topic';

    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'category_slug' => ['required', 'string', 'exists:categories,slug'],
            'title' => ['required', 'string', 'max:180'],
            'hook' => ['nullable', 'string', 'max:280'],
            'summary' => ['nullable', 'string'],
            'what_changes' => ['nullable', 'string'],
            'who_affected' => ['nullable', 'string'],
            'before_text' => ['nullable', 'string'],
            'after_text' => ['nullable', 'string'],
            'detail' => ['nullable', 'string'],
        ]);

        $category = Category::query()->where('slug', $data['category_slug'])->firstOrFail();

        $case = EditorialCase::query()->create([
            'category_id' => $category->id,
            'slug' => $this->uniqueSlug($data['title']),
            'title' => $data['title'],
            'hook' => $data['hook'] ?? null,
            'summary' => $data['summary'] ?? null,
            'what_changes' => $data['what_changes'] ?? null,
            'who_affected' => $data['who_affected'] ?? null,
            'before_text' => $data['before_text'] ?? null,
            'after_text' => $data['after_text'] ?? null,
            'detail' => $data['detail'] ?? null,
            'areas' => [$category->slug],
            'procedural_status' => 'en_tramitacion',
            'public_status_label' => 'en_discusion',
            'evidence_status' => 'unknown',
            'observed_effect' => 'unknown',
            'publication_status' => 'published',
            'published_at' => now(),
            'content_version' => 1,
        ]);

        return Response::json($case->toPublicArray());
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'category_slug' => $schema->string()->description('Slug devuelto por list_categories.')->required(),
            'title' => $schema->string()->description('Titular corto que nombra el programa, derecho o servicio afectado. No empieces con «Presupuesto 2027» ni copies la fórmula de otro caso; mira list_topics.')->required(),
            'hook' => $schema->string()->description('Una frase para enganchar. Obligatorio usar marcas visuales: **negrita**, {-pérdida}, {+aumento o continuidad}. El $ solo abre una cifra en pesos ({-$1.896 millones}); no lo pongas delante de una palabra. No texto plano.'),
            'summary' => $schema->string()->description('Resumen breve, con el mismo formato visual si hay cifra o límite.'),
            'what_changes' => $schema->string()->description('Qué cambia, en una o dos frases, con {-…} / {+…} / **…** donde corresponda.'),
            'who_affected' => $schema->string()->description('A quién afecta, en una frase, con marcas visuales si hay cifra o grupo clave.'),
            'before_text' => $schema->string()->description('Qué había antes. Usa {+…} para la línea base y **…** para el estado.'),
            'after_text' => $schema->string()->description('Qué queda con el cambio. Usa {-…} para recortes y **…** si aún no es ley.'),
            'detail' => $schema->string()->description('Profundidad en bloques ##. Empieza por el trámite. No prosa plana: **negrita**, {-pérdida}, {+continuidad}, referencias [n] y enlaces internos [[slug|texto]] a otras notas del portal.'),
        ];
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        if ($base === '') {
            $base = 'tema';
        }

        $slug = $base;
        $i = 2;

        while (EditorialCase::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
