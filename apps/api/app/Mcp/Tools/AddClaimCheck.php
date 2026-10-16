<?php

namespace App\Mcp\Tools;

use App\Models\EditorialCase;
use App\Support\ClaimChecks;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Registra lo que circula sobre un tema publicado —un comentario o una idea que se da por cierta— y la respuesta con fuentes. El veredicto dice si se sostiene, no se sostiene, queda a medias o falta el dato. No guarda nombres de personas ni hilos de comentarios: parafrasea la idea.')]
class AddClaimCheck extends Tool
{
    protected string $name = 'add_claim_check';

    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'slug' => ['required', 'string'],
            'claim' => ['required', 'string', 'max:500'],
            'verdict' => ['required', 'string', Rule::in(ClaimChecks::verdictKeys())],
            'response' => ['required', 'string', 'max:2000'],
            'heard_on' => ['nullable', 'string', 'max:160'],
            'source_url' => ['nullable', 'url', 'max:2048'],
            'source_title' => ['required_with:source_url', 'nullable', 'string', 'max:180'],
            'source_kind' => ['nullable', 'string', Rule::in(AddSource::KINDS)],
        ]);

        if ($data['verdict'] !== 'sin_respaldo' && empty($data['source_url'])) {
            throw ValidationException::withMessages([
                'source_url' => 'Esta respuesta necesita un enlace que acredite o contradiga lo que se comenta.',
            ]);
        }

        $case = EditorialCase::query()->published()->where('slug', $data['slug'])->first();

        if (! $case) {
            return Response::error('No hay un tema publicado con ese slug.');
        }

        $claimKey = ClaimChecks::key($data['claim']);

        if ($case->claims()->where('claim_key', $claimKey)->exists()) {
            return Response::json([
                'added' => false,
                'reason' => 'Esa idea ya está contrastada en este tema.',
                'topic' => $case->fresh()->toPublicArray(),
            ]);
        }

        try {
            DB::transaction(function () use ($case, $data, $claimKey) {
                $this->attachSource($case, $data);

                $nextOrder = (int) $case->claims()->max('sort_order') + 1;
                $case->claims()->create([
                    'claim_key' => $claimKey,
                    'claim' => trim($data['claim']),
                    'verdict' => $data['verdict'],
                    'response' => trim($data['response']),
                    'heard_on' => isset($data['heard_on']) ? trim($data['heard_on']) : null,
                    'source_url' => $data['source_url'] ?? null,
                    'source_title' => $data['source_title'] ?? null,
                    'sort_order' => $nextOrder,
                ]);

                $case->forceFill([
                    'sources_count' => $case->sources()->count(),
                    'content_version' => (int) $case->content_version + 1,
                    'reviewed_at' => now(),
                ])->save();
            });
        } catch (UniqueConstraintViolationException) {
            return Response::json([
                'added' => false,
                'reason' => 'Esa idea ya está contrastada en este tema.',
                'topic' => $case->fresh()->toPublicArray(),
            ]);
        }

        return Response::json([
            'added' => true,
            'topic' => $case->fresh()->toPublicArray(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function attachSource(EditorialCase $case, array $data): void
    {
        $url = $data['source_url'] ?? null;
        if (! is_string($url) || $url === '') {
            return;
        }

        if ($case->sources()->where('url', $url)->exists()) {
            return;
        }

        $kind = $data['source_kind'] ?? 'prensa';
        $nextOrder = (int) $case->sources()->max('sort_order') + 1;

        $case->sources()->create([
            'title' => $data['source_title'],
            'url' => $url,
            'kind' => $kind,
            'is_primary' => in_array($kind, ['documento', 'ley'], true),
            'sort_order' => $nextOrder,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()->description('Slug del tema publicado.')->required(),
            'claim' => $schema->string()->description('La idea que circula, parafraseada. Sin nombres de cuentas ni citas literales de una persona.')->required(),
            'verdict' => $schema->string()->description('se_sostiene, no_se_sostiene, a_medias o sin_respaldo.')->required(),
            'response' => $schema->string()->description('Respuesta con el dato. Puede usar **negrita**, {-pérdida}, {+continuidad} y [n] si la fuente ya está numerada.')->required(),
            'heard_on' => $schema->string()->description('Dónde circula, sin identificar personas. Ej.: comentarios en redes, nota de un medio.'),
            'source_url' => $schema->string()->description('Enlace que acredita o contradice la idea. Obligatorio salvo sin_respaldo.'),
            'source_title' => $schema->string()->description('Nombre de esa fuente. Obligatorio si hay source_url.'),
            'source_kind' => $schema->string()->description('documento, prensa, noticia, ley o analisis. Por defecto prensa. Si la URL ya está en el tema, no se duplica.'),
        ];
    }
}
