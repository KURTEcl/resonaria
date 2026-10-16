<?php

namespace App\Mcp\Tools;

use App\Models\CaseLink;
use App\Models\EditorialCase;
use App\Support\TopicLinks;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Asocia dos notas publicadas del portal, como Wikipedia. slug apunta a to_slug: contexto si la otra explica esta, consecuencia si es un efecto posterior, antecedente si vino antes, o relacionado. Devuelve el markup [[to_slug|ancla]] para incrustarlo en hook o detail. No duplica el mismo par.')]
class AddLink extends Tool
{
    protected string $name = 'add_link';

    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'slug' => ['required', 'string'],
            'to_slug' => ['required', 'string'],
            'relation' => ['nullable', 'string', Rule::in(TopicLinks::relationKeys())],
            'note' => ['nullable', 'string', 'max:240'],
            'anchor' => ['nullable', 'string', 'max:120'],
        ]);

        if ($data['slug'] === $data['to_slug']) {
            return Response::error('Una nota no se asocia consigo misma.');
        }

        $from = EditorialCase::query()->published()->where('slug', $data['slug'])->first();
        if (! $from) {
            return Response::error('No hay un tema publicado con ese slug.');
        }

        $to = EditorialCase::query()->published()->where('slug', $data['to_slug'])->first();
        if (! $to) {
            return Response::error('No hay una nota publicada con el slug de destino. Usa list_topics para encontrar el slug.');
        }

        $relation = $data['relation'] ?? 'relacionado';
        $note = isset($data['note']) ? trim($data['note']) : null;
        $note = $note === '' ? null : $note;
        $anchor = trim($data['anchor'] ?? '') ?: $to->title;
        $markup = TopicLinks::markup($to->slug, $anchor);

        $existing = CaseLink::query()
            ->where('from_case_id', $from->id)
            ->where('to_case_id', $to->id)
            ->first();

        if ($existing && $existing->relation === $relation && $existing->note === $note && $existing->origin === 'tool') {
            return Response::json([
                'added' => false,
                'reason' => 'Esas notas ya están asociadas.',
                'markup' => $markup,
                'topic' => $from->fresh()->toPublicArray(),
            ]);
        }

        CaseLink::query()->updateOrCreate(
            [
                'from_case_id' => $from->id,
                'to_case_id' => $to->id,
            ],
            [
                'relation' => $relation,
                'note' => $note,
                'origin' => 'tool',
            ],
        );

        $from->forceFill([
            'content_version' => (int) $from->content_version + 1,
            'reviewed_at' => now(),
        ])->save();

        return Response::json([
            'added' => true,
            'markup' => $markup,
            'topic' => $from->fresh()->toPublicArray(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()->description('Slug de la nota que apunta. Usa list_topics.')->required(),
            'to_slug' => $schema->string()->description('Slug de la nota destino, ya publicada.')->required(),
            'relation' => $schema->string()->description('contexto, consecuencia, antecedente o relacionado. Por defecto relacionado.'),
            'note' => $schema->string()->description('Por qué se asocian, en una frase. Ej.: el recorte a estudios de suelo agrava el riesgo del temporal.'),
            'anchor' => $schema->string()->description('Texto visible del enlace interno. Si omites, usa el título de la nota destino. Incrusta el markup devuelto en hook o detail.'),
        ];
    }
}
