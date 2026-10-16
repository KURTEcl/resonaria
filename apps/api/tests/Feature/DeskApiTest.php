<?php

namespace Tests\Feature;

use App\Models\EditorialCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeskApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['desk.gate' => 'puerta-de-prueba']);
    }

    public function test_gate_and_login_stay_closed_without_the_secret(): void
    {
        User::factory()->create([
            'email' => 'mesa@queperdimos.local',
            'password' => 'clave-larga-mesa',
        ]);

        $this->postJson('/api/v1/desk/gate', ['gate' => 'otra'])->assertNotFound();

        $this->postJson('/api/v1/desk/session', [
            'gate' => 'otra',
            'email' => 'mesa@queperdimos.local',
            'password' => 'clave-larga-mesa',
        ])->assertUnauthorized();
    }

    public function test_operator_can_withdraw_and_edit_a_published_topic(): void
    {
        $user = User::factory()->create([
            'email' => 'mesa@queperdimos.local',
            'password' => 'clave-larga-mesa',
        ]);

        EditorialCase::query()->create([
            'slug' => 'beca-demo',
            'title' => 'Beca demo',
            'hook' => 'Gancho viejo',
            'summary' => 'Resumen viejo',
            'procedural_status' => 'vigente',
            'publication_status' => 'published',
            'published_at' => now(),
        ]);

        $this->postJson('/api/v1/desk/gate', ['gate' => 'puerta-de-prueba'])->assertOk();

        $token = $this->postJson('/api/v1/desk/session', [
            'gate' => 'puerta-de-prueba',
            'email' => 'mesa@queperdimos.local',
            'password' => 'clave-larga-mesa',
        ])->assertOk()->json('token');

        $this->withToken($token)
            ->patchJson('/api/v1/desk/cases/beca-demo', [
                'publication_status' => 'draft',
                'hook' => 'Gancho nuevo',
            ])
            ->assertOk()
            ->assertJsonPath('data.publication_status', 'draft')
            ->assertJsonPath('data.hook', 'Gancho nuevo');

        $this->getJson('/api/v1/cases/beca-demo')->assertNotFound();

        $this->app['auth']->forgetGuards();

        $this->withToken($user->createToken('mcp')->plainTextToken)
            ->getJson('/api/v1/desk/cases')
            ->assertUnauthorized();
    }
}
