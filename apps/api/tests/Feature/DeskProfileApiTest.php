<?php

namespace Tests\Feature;

use App\Models\EditorialCase;
use App\Models\ProfileMention;
use App\Models\PublicProfile;
use App\Models\User;
use App\Support\ProfileImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeskProfileApiTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        config(['desk.gate' => 'puerta-de-prueba']);

        User::factory()->create([
            'email' => 'mesa@queperdimos.local',
            'password' => 'clave-larga-mesa',
        ]);

        $this->token = $this->postJson('/api/v1/desk/session', [
            'gate' => 'puerta-de-prueba',
            'email' => 'mesa@queperdimos.local',
            'password' => 'clave-larga-mesa',
        ])->assertOk()->json('token');
    }

    public function test_desk_lists_profiles_and_confirms_mention(): void
    {
        (new ProfileImporter)->import([
            'slug' => 'perfil-desk',
            'display_name' => 'Perfil Desk',
        ]);

        $case = EditorialCase::query()->create([
            'slug' => 'tema-desk',
            'title' => 'Tema desk',
            'procedural_status' => 'vigente',
            'publication_status' => 'published',
            'published_at' => now(),
        ]);

        $mention = ProfileMention::query()->create([
            'profile_id' => PublicProfile::query()->where('slug', 'perfil-desk')->value('id'),
            'mentionable_type' => EditorialCase::class,
            'mentionable_id' => $case->id,
            'role' => 'mencionado',
            'origin' => 'scan',
            'confidence' => 'suggested',
        ]);

        $this->withToken($this->token)
            ->getJson('/api/v1/desk/profiles')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'perfil-desk');

        $this->withToken($this->token)
            ->getJson('/api/v1/desk/profile-mentions?suggested=1')
            ->assertOk()
            ->assertJsonPath('data.0.id', $mention->id);

        $this->withToken($this->token)
            ->patchJson('/api/v1/desk/profile-mentions/'.$mention->id, ['confidence' => 'confirmed'])
            ->assertOk()
            ->assertJsonPath('data.confidence', 'confirmed');
    }
}
