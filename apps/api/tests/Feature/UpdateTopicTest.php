<?php

namespace Tests\Feature;

use App\Mcp\Tools\UpdateTopic;
use App\Models\Category;
use App\Models\EditorialCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Mcp\Request;
use Tests\TestCase;

class UpdateTopicTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_before_and_after_text(): void
    {
        $case = $this->publishedCase();

        $response = (new UpdateTopic)->handle(new Request([
            'slug' => $case->slug,
            'before_text' => 'En 2026 el programa tenía {+$8 mil millones}.',
            'after_text' => 'El proyecto deja {-43,6%} en el capítulo.',
        ]));

        $this->assertFalse($response->isError());
        $fresh = $case->fresh();
        $this->assertSame('En 2026 el programa tenía {+$8 mil millones}.', $fresh->before_text);
        $this->assertSame('El proyecto deja {-43,6%} en el capítulo.', $fresh->after_text);
        $this->assertSame(2, $fresh->content_version);
        $this->assertNotNull($fresh->reviewed_at);
    }

    public function test_it_drops_a_peso_sign_that_does_not_open_a_number(): void
    {
        $case = $this->publishedCase();

        $response = (new UpdateTopic)->handle(new Request([
            'slug' => $case->slug,
            'what_changes' => 'Advierte {-$menos sedes PAES} y deja {-$1.896 millones}.',
        ]));

        $this->assertFalse($response->isError());
        $this->assertSame(
            'Advierte {-menos sedes PAES} y deja {-$1.896 millones}.',
            $case->fresh()->what_changes,
        );
    }

    public function test_it_rejects_an_unknown_topic(): void
    {
        $response = (new UpdateTopic)->handle(new Request([
            'slug' => 'no-existe',
            'before_text' => '{+$1}',
        ]));

        $this->assertTrue($response->isError());
    }

    public function test_it_updates_the_title_without_changing_the_slug(): void
    {
        $case = $this->publishedCase();

        $response = (new UpdateTopic)->handle(new Request([
            'slug' => $case->slug,
            'title' => 'INDAP: proponen recortar 13% su presupuesto',
        ]));

        $this->assertFalse($response->isError());
        $fresh = $case->fresh();
        $this->assertSame('INDAP: proponen recortar 13% su presupuesto', $fresh->title);
        $this->assertSame('tema-de-prueba', $fresh->slug);
    }

    private function publishedCase(): EditorialCase
    {
        $category = Category::query()->create([
            'slug' => 'estado-y-servicios',
            'title' => 'Estado y servicios',
            'lead' => 'Cambios en servicios e instituciones del Estado.',
            'sort' => 1,
        ]);

        return EditorialCase::query()->create([
            'category_id' => $category->id,
            'slug' => 'tema-de-prueba',
            'title' => 'Tema de prueba',
            'before_text' => 'Texto previo sin marcas.',
            'procedural_status' => 'en_tramitacion',
            'public_status_label' => 'en_discusion',
            'publication_status' => 'published',
            'published_at' => now(),
            'content_version' => 1,
            'sources_count' => 0,
        ]);
    }
}
