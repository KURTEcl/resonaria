<?php

namespace App\Mcp\Tools;

use App\Models\EditorialCase;
use App\Support\CaseUpdates;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Agrega un hito de seguimiento a un tema publicado: trámite, votación, anuncio, conversación pública o fuente. Marca si la propuesta negativa avanza (riesgo), si hay señales de reversión (reversion) o si aún no se ve el rumbo (incierto). No reescribe la ficha completa.')]
class AddUpdate extends Tool
{
    protected string $name = 'add_update';

    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'slug' => ['required', 'string'],
            'label' => ['required', 'string', 'max:240'],
            'occurred_on' => ['nullable', 'date'],
            'occurred_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:800'],
            'kind' => ['nullable', 'string', Rule::in(CaseUpdates::kindKeys())],
            'direction' => ['nullable', 'string', Rule::in(CaseUpdates::directionKeys())],
            'source_url' => ['nullable', 'url', 'max:2048'],
            'source_title' => ['nullable', 'string', 'max:180'],
            'procedural_status' => ['nullable', 'string', Rule::in(CaseUpdates::proceduralKeys())],
            'public_status_label' => ['nullable', 'string', Rule::in(CaseUpdates::publicStatusKeys())],
        ]);

        $case = EditorialCase::query()->published()->where('slug', $data['slug'])->first();

        if (! $case) {
            return Response::error('No hay un tema publicado con ese slug.');
        }

        $nextOrder = (int) $case->milestones()->max('sort_order') + 1;

        $occurredAt = isset($data['occurred_at']) ? Carbon::parse($data['occurred_at']) : null;
        $occurredOn = $data['occurred_on'] ?? ($occurredAt?->toDateString() ?? now()->toDateString());

        $case->milestones()->create([
            'occurred_on' => $occurredOn,
            'occurred_at' => $occurredAt,
            'kind' => $data['kind'] ?? 'otro',
            'label' => $data['label'],
            'note' => $data['note'] ?? null,
            'direction' => $data['direction'] ?? 'incierto',
            'source_url' => $data['source_url'] ?? null,
            'source_title' => $data['source_title'] ?? null,
            'procedural_status' => $data['procedural_status'] ?? $case->procedural_status,
            'sort_order' => $nextOrder,
        ]);

        $case->fill([
            'content_version' => (int) $case->content_version + 1,
            'reviewed_at' => now(),
        ]);

        if (! empty($data['procedural_status'])) {
            $case->procedural_status = $data['procedural_status'];
        }

        if (! empty($data['public_status_label'])) {
            $case->public_status_label = $data['public_status_label'];
        }

        $case->save();

        return Response::json($case->fresh()->toPublicArray());
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()->description('Slug del tema publicado.')->required(),
            'label' => $schema->string()->description('Qué ocurrió, en una frase.')->required(),
            'occurred_on' => $schema->string()->description('Fecha del hito (YYYY-MM-DD). Si se omite, se usa hoy o la fecha de occurred_at.'),
            'occurred_at' => $schema->string()->description('Fecha y hora del hito (ISO 8601, ej. 2026-10-13T16:30:00-03:00). Útil en noticias en desarrollo.'),
            'note' => $schema->string()->description('Contexto breve: qué cambia en el trámite o en la conversación.'),
            'kind' => $schema->string()->description('tramite, votacion, anuncio, redes, fuente u otro.'),
            'direction' => $schema->string()->description('riesgo (la medida avanza), reversion (puede revertirse) o incierto.'),
            'source_url' => $schema->string()->description('Enlace público al documento, nota o publicación.'),
            'source_title' => $schema->string()->description('Nombre corto de esa fuente.'),
            'procedural_status' => $schema->string()->description('Si el trámite cambió: anunciada, propuesta_formal, en_tramitacion, aprobada_pendiente, vigente, en_aplicacion, suspendida, revertida.'),
            'public_status_label' => $schema->string()->description('Si el estado público cambió: en_discusion, aprobado, en_aplicacion, revertido.'),
        ];
    }
}
