<?php

namespace App\Mcp\Tools;

use App\Models\CaseClaim;
use App\Models\CaseMilestone;
use App\Models\CaseSource;
use App\Models\EditorialCase;
use App\Models\ProfileMention;
use App\Models\Reaction;
use App\Models\PublicProfile;
use App\Support\ProfileProvisioner;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Vincula un perfil a un tema publicado o a fuente, hito, reacción o claim. Si el slug no existe, pasa display_name (y kind opcional) para crear el perfil activo. Reactiva perfiles archivados. La mención queda confirmada.')]
class AddProfileMention extends Tool
{
    protected string $name = 'add_profile_mention';

    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'profile_slug' => ['required', 'string'],
            'topic_slug' => ['required', 'string'],
            'target' => ['nullable', 'string', Rule::in(['topic', 'source', 'milestone', 'reaction', 'claim'])],
            'target_id' => ['nullable', 'integer'],
            'role' => ['nullable', 'string', Rule::in(array_keys(config('profiles.mention_roles', [])))],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'context_note' => ['nullable', 'string', 'max:240'],
            'display_name' => ['nullable', 'string', 'max:180'],
            'kind' => ['nullable', 'string', Rule::in(array_keys(config('profiles.kinds', [])))],
            'aliases' => ['nullable', 'array'],
            'aliases.*' => ['string', 'max:240'],
        ]);

        $normalizedSlug = Str::slug($data['profile_slug']);
        $existingProfile = PublicProfile::query()->where('slug', $normalizedSlug)->first();

        try {
            $profile = (new ProfileProvisioner)->ensureActiveForMention(
                $data['profile_slug'],
                $data['display_name'] ?? null,
                $data['kind'] ?? null,
                $data['aliases'] ?? [],
            );
        } catch (InvalidArgumentException $exception) {
            return Response::error($exception->getMessage());
        }

        $profileCreated = $existingProfile === null;
        $profileReactivated = $existingProfile !== null && $existingProfile->status !== 'active';

        $case = EditorialCase::query()->published()->where('slug', $data['topic_slug'])->first();
        if (! $case) {
            return Response::error('No hay un tema publicado con ese slug.');
        }

        $target = $data['target'] ?? 'topic';
        $model = match ($target) {
            'topic' => $case,
            'source' => CaseSource::query()->where('case_id', $case->id)->where('id', $data['target_id'] ?? 0)->first(),
            'milestone' => CaseMilestone::query()->where('case_id', $case->id)->where('id', $data['target_id'] ?? 0)->first(),
            'reaction' => Reaction::query()->where('case_id', $case->id)->where('id', $data['target_id'] ?? 0)->first(),
            'claim' => CaseClaim::query()->where('case_id', $case->id)->where('id', $data['target_id'] ?? 0)->first(),
            default => null,
        };

        if (! $model) {
            return Response::error('No se encontró el artefacto indicado en ese tema.');
        }

        $existing = ProfileMention::query()
            ->where('profile_id', $profile->id)
            ->where('mentionable_type', $model::class)
            ->where('mentionable_id', $model->id)
            ->first();

        if ($existing) {
            return Response::json([
                'added' => false,
                'reason' => 'Esa mención ya existe.',
                'mention' => [
                    'profile_slug' => $profile->slug,
                    'role' => $existing->role,
                    'confidence' => $existing->confidence,
                ],
            ]);
        }

        $mention = ProfileMention::query()->create([
            'profile_id' => $profile->id,
            'mentionable_type' => $model::class,
            'mentionable_id' => $model->id,
            'role' => $data['role'] ?? 'mencionado',
            'origin' => 'mcp',
            'confidence' => 'confirmed',
            'excerpt' => $data['excerpt'] ?? null,
            'context_note' => $data['context_note'] ?? null,
        ]);

        return Response::json([
            'added' => true,
            'profile_created' => $profileCreated,
            'profile_reactivated' => $profileReactivated,
            'profile' => [
                'slug' => $profile->slug,
                'display_name' => $profile->display_name,
                'kind' => $profile->kind,
                'status' => $profile->status,
            ],
            'mention' => $mention->toDeskArray(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'profile_slug' => $schema->string()->description('Slug del perfil (existente o nuevo; se normaliza). Si no existe, se crea activo al vincular.'),
            'topic_slug' => $schema->string()->description('Slug del tema publicado.'),
            'target' => $schema->string()->description('topic (por defecto), source, milestone, reaction o claim.'),
            'target_id' => $schema->integer()->description('ID del artefacto cuando target no es topic.'),
            'role' => $schema->string()->description('protagonista, mencionado, autor_declaracion o responsable_cartera.'),
            'excerpt' => $schema->string()->description('Fragmento opcional donde aparece.'),
            'context_note' => $schema->string()->description('Nota editorial breve.'),
            'display_name' => $schema->string()->description('Nombre público si el perfil es nuevo (recomendado; si falta, se infiere del slug).'),
            'kind' => $schema->string()->description('Tipo de figura: Estado (ejecutivo, diputado, senador, gobernador, alcalde, concejal, seremi, funcionario), política (dirigente), sociedad (lider), medios/expertos (periodista, economista, comentarista, profesional), interes_publico u otro. Default otro. Ver kind_options en list_profiles.'),
            'aliases' => $schema->array()->description('Alias extra para escaneo futuro (nombres alternativos).'),
        ];
    }
}
