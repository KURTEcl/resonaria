<?php

namespace Tests\Feature;

use App\Mcp\Tools\AddProfileMention;
use App\Models\EditorialCase;
use App\Support\ProfileImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Mcp\Request;
use Tests\TestCase;

class ProfilePublicApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_perfiles_and_case_profiles_array(): void
    {
        (new ProfileImporter)->import([
            'slug' => 'figura-publica',
            'display_name' => 'Figura Pública',
        ]);

        EditorialCase::query()->create([
            'slug' => 'tema-publico',
            'title' => 'Tema público',
            'procedural_status' => 'vigente',
            'publication_status' => 'published',
            'published_at' => now(),
        ]);

        (new AddProfileMention)->handle(new Request([
            'profile_slug' => 'figura-publica',
            'topic_slug' => 'tema-publico',
            'role' => 'protagonista',
        ]));

        $this->getJson('/api/v1/perfiles')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'figura-publica');

        $this->getJson('/api/v1/perfiles/figura-publica')
            ->assertOk()
            ->assertJsonPath('data.slug', 'figura-publica')
            ->assertJsonPath('data.topics.0.slug', 'tema-publico');

        $this->getJson('/api/v1/cases/tema-publico')
            ->assertOk()
            ->assertJsonPath('data.profiles.0.slug', 'figura-publica');

        $list = collect($this->getJson('/api/v1/cases')->assertOk()->json('data'));
        $row = $list->firstWhere('slug', 'tema-publico');
        $this->assertNotNull($row);
        $this->assertSame('figura-publica', $row['profiles'][0]['slug']);

        $this->getJson('/api/v1/cases?perfil=figura-publica')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'tema-publico');

        $this->getJson('/api/v1/cases?perfil=sin-vinculos')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
