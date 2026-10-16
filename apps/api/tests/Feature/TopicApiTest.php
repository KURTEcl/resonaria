<?php

namespace Tests\Feature;

use App\Mcp\Tools\AddReaction;
use App\Mcp\Tools\CreateTopic;
use App\Models\Category;
use App\Models\EditorialCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Mcp\Request;
use Tests\TestCase;

class TopicApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_topic_includes_category_and_reactions(): void
    {
        $category = Category::query()->create([
            'slug' => 'educacion',
            'title' => 'Educación',
            'lead' => 'Cambios en el acceso a la educación y sus apoyos.',
            'sort' => 1,
        ]);

        $case = EditorialCase::query()->create([
            'category_id' => $category->id,
            'slug' => 'computadores-demo',
            'title' => 'Computadores escolares en riesgo',
            'hook' => 'Puede cortarse la entrega de equipos.',
            'what_changes' => 'La partida no aparece comparable.',
            'who_affected' => 'Estudiantes de escuelas públicas.',
            'before_text' => 'Hay un programa vigente.',
            'after_text' => 'El proyecto no muestra la partida.',
            'detail' => "Primer párrafo.\n\nSegundo párrafo.",
            'procedural_status' => 'en_tramitacion',
            'public_status_label' => 'en_discusion',
            'publication_status' => 'published',
            'published_at' => now(),
        ]);

        $case->reactions()->create([
            'platform' => 'tiktok',
            'url' => 'https://www.tiktok.com/@queperdimos/video/7482910033445566777',
            'caption' => 'Ejemplo.',
            'author_handle' => '@queperdimos',
        ]);

        $this->getJson('/api/v1/cases/computadores-demo')
            ->assertOk()
            ->assertJsonPath('data.category.slug', 'educacion')
            ->assertJsonPath('data.hook', 'Puede cortarse la entrega de equipos.')
            ->assertJsonPath('data.reactions.0.platform', 'tiktok');

        $this->getJson('/api/v1/cases?area=educacion')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'computadores-demo');

        $this->getJson('/api/v1/cases?pieza=actualidad')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'computadores-demo');

        $this->getJson('/api/v1/cases?pieza=seguimiento')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_mcp_requires_a_token_and_can_publish_a_topic(): void
    {
        Category::query()->create([
            'slug' => 'salud',
            'title' => 'Salud',
            'lead' => 'Cambios en atención, cobertura y prestaciones de salud.',
            'sort' => 1,
        ]);

        $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/list',
        ])->assertUnauthorized();

        $user = User::factory()->create();
        $token = $user->createToken('mcp')->plainTextToken;

        $this->withToken($token)
            ->postJson('/mcp', [
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'tools/list',
            ])
            ->assertOk()
            ->assertSee('create_topic', false)
            ->assertSee('add_source', false)
            ->assertSee('add_update', false)
            ->assertSee('add_link', false);

        $created = (new CreateTopic)->handle(new Request([
            'category_slug' => 'salud',
            'title' => 'Listas de espera',
            'hook' => 'La meta de espera cambia y nadie explica el efecto.',
        ]));

        $this->assertNotNull(EditorialCase::query()->where('slug', 'listas-de-espera')->first());

        (new AddReaction)->handle(new Request([
            'slug' => 'listas-de-espera',
            'platform' => 'x',
            'url' => 'https://x.com/queperdimos/status/1842200000000000003',
            'caption' => 'Ejemplo.',
        ]));

        $this->assertSame(1, EditorialCase::query()->where('slug', 'listas-de-espera')->first()->reactions()->count());
        $this->assertNotNull($created);
    }
}
