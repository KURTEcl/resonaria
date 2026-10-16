<?php

namespace Tests\Feature;

use App\Mcp\Tools\AddProfileMention;
use App\Models\EditorialCase;
use App\Models\ProfileMention;
use App\Models\PublicProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Tests\TestCase;

class AddProfileMentionProvisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_active_profile_when_missing(): void
    {
        EditorialCase::query()->create([
            'slug' => 'tema-nuevo-perfil',
            'title' => 'Tema',
            'procedural_status' => 'vigente',
            'publication_status' => 'published',
            'published_at' => now(),
        ]);

        $response = (new AddProfileMention)->handle(new Request([
            'profile_slug' => 'diputada-ejemplo',
            'display_name' => 'Diputada Ejemplo',
            'kind' => 'legislador',
            'topic_slug' => 'tema-nuevo-perfil',
            'role' => 'protagonista',
        ]));

        $this->assertFalse($response->isError());
        $this->assertDatabaseHas('public_profiles', [
            'slug' => 'diputada-ejemplo',
            'status' => 'active',
            'kind' => 'legislador',
        ]);

        $profile = PublicProfile::query()->where('slug', 'diputada-ejemplo')->first();
        $this->assertTrue(ProfileMention::query()
            ->where('profile_id', $profile->id)
            ->where('confidence', 'confirmed')
            ->exists());
    }

    public function test_accepts_media_and_expert_kinds(): void
    {
        EditorialCase::query()->create([
            'slug' => 'tema-periodista',
            'title' => 'Tema',
            'procedural_status' => 'vigente',
            'publication_status' => 'published',
            'published_at' => now(),
        ]);

        foreach ([
            'diputado', 'senador', 'gobernador', 'funcionario', 'dirigente', 'lider',
            'periodista', 'economista', 'comentarista', 'alcalde', 'seremi', 'interes_publico',
        ] as $kind) {
            $slug = Str::slug("figura-{$kind}");
            $response = (new AddProfileMention)->handle(new Request([
                'profile_slug' => $slug,
                'display_name' => "Figura {$kind}",
                'kind' => $kind,
                'topic_slug' => 'tema-periodista',
            ]));
            $this->assertFalse($response->isError(), "kind {$kind} should be valid");
            $this->assertDatabaseHas('public_profiles', ['slug' => $slug, 'kind' => $kind]);
        }
    }

    public function test_infers_display_name_from_slug_when_missing(): void
    {
        EditorialCase::query()->create([
            'slug' => 'tema-inferido',
            'title' => 'Tema',
            'procedural_status' => 'vigente',
            'publication_status' => 'published',
            'published_at' => now(),
        ]);

        $response = (new AddProfileMention)->handle(new Request([
            'profile_slug' => 'diego-vergara',
            'kind' => 'profesional',
            'topic_slug' => 'tema-inferido',
        ]));

        $this->assertFalse($response->isError());
        $this->assertDatabaseHas('public_profiles', [
            'slug' => 'diego-vergara',
            'display_name' => 'Diego Vergara',
            'status' => 'active',
        ]);
    }

    public function test_reactivates_archived_profile(): void
    {
        $profile = PublicProfile::query()->create([
            'slug' => 'figura-archivada',
            'display_name' => 'Figura Archivada',
            'status' => 'archived',
        ]);

        EditorialCase::query()->create([
            'slug' => 'tema-reactiva',
            'title' => 'Tema',
            'procedural_status' => 'vigente',
            'publication_status' => 'published',
            'published_at' => now(),
        ]);

        (new AddProfileMention)->handle(new Request([
            'profile_slug' => $profile->slug,
            'topic_slug' => 'tema-reactiva',
        ]));

        $this->assertSame('active', $profile->fresh()->status);
    }
}
