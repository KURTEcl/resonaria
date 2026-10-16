<?php

namespace App\Mcp\Tools;

use App\Models\EditorialCase;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Actualiza el texto de un tema publicado (título, gancho, resumen, qué cambia, a quién afecta, antes/después y detalle). No crea un tema nuevo ni cambia el slug.')]
class UpdateTopic extends Tool
{
    protected string $name = 'update_topic';

    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'slug' => ['required', 'string'],
            'title' => ['nullable', 'string', 'max:180'],
            'hook' => ['nullable', 'string', 'max:280'],
            'summary' => ['nullable', 'string'],
            'what_changes' => ['nullable', 'string'],
            'who_affected' => ['nullable', 'string'],
            'before_text' => ['nullable', 'string'],
            'after_text' => ['nullable', 'string'],
            'detail' => ['nullable', 'string'],
        ]);

        $case = EditorialCase::query()->published()->where('slug', $data['slug'])->first();

        if (! $case) {
            return Response::error('No hay un tema publicado con ese slug.');
        }

        $fields = ['title', 'hook', 'summary', 'what_changes', 'who_affected', 'before_text', 'after_text', 'detail'];
        $payload = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== null) {
                $payload[$field] = $data[$field];
            }
        }

        if ($payload === []) {
            return Response::error('No hay campos para actualizar.');
        }

        $case->forceFill([
            ...$payload,
            'content_version' => (int) $case->content_version + 1,
            'reviewed_at' => now(),
        ])->save();

        return Response::json($case->fresh()->toPublicArray());
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()->description('Slug del tema publicado.')->required(),
            'title' => $schema->string()->description('Titular visible. No cambia el slug ni la URL.'),
            'hook' => $schema->string()->description('Gancho. Usa **negrita**, {-pérdida} y {+aumento o continuidad}. El $ solo abre una cifra en pesos; no lo pongas delante de una palabra.'),
            'summary' => $schema->string()->description('Resumen breve, con el mismo formato visual si hay cifra o límite.'),
            'what_changes' => $schema->string()->description('Qué cambia, con {-…} / {+…} / **…**.'),
            'who_affected' => $schema->string()->description('A quién afecta, con marcas visuales si hay cifra o grupo clave.'),
            'before_text' => $schema->string()->description('Hasta ahora. Usa {+…} para la línea base; no mezcles {-…} o la columna pierde el verde.'),
            'after_text' => $schema->string()->description('Si se aprueba así. Usa {-…} para recortes; no mezcles {+…} o la columna pierde el rojo.'),
            'detail' => $schema->string()->description('Profundidad en bloques ##, con **negrita**, {-pérdida}, {+continuidad} y enlaces internos [[slug|texto]] a otras notas del portal.'),
        ];
    }
}
