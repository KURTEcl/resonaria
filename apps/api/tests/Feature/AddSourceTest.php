<?php

namespace Tests\Feature;

use App\Mcp\Tools\AddSource;
use App\Models\Category;
use App\Models\EditorialCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Mcp\Request;
use Tests\TestCase;

class AddSourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_adds_a_news_source_to_a_published_topic(): void
    {
        $case = $this->publishedCase();

        $response = (new AddSource)->handle(new Request([
            'slug' => $case->slug,
            'title' => 'Radio U. de Chile — recorte a la Ley Cholito',
            'url' => 'https://radio.uchile.cl/2026/10/06/gobierno-propone-profundo-recorte-a-ley-cholito-recursos-caerian-en-un-86-para-2027/',
            'kind' => 'prensa',
            'locator' => 'Cifra de 86% y montos 2026-2027',
            'published_on' => '2026-10-06',
        ]));

        $this->assertFalse($response->isError());
        $this->assertSame(1, $case->fresh()->sources_count);
        $this->assertSame(2, $case->fresh()->content_version);

        $this->getJson('/api/v1/cases/'.$case->slug)
            ->assertOk()
            ->assertJsonPath('data.sources.0.title', 'Radio U. de Chile — recorte a la Ley Cholito')
            ->assertJsonPath('data.sources.0.kind', 'prensa')
            ->assertJsonPath('data.sources.0.is_primary', false);
    }

    public function test_it_skips_a_duplicate_url(): void
    {
        $case = $this->publishedCase();
        $url = 'https://www.dipres.gob.cl/597/w3-multipropertyvalues-15168-38403.html';

        (new AddSource)->handle(new Request([
            'slug' => $case->slug,
            'title' => 'DIPRES — proyecto 2027',
            'url' => $url,
            'kind' => 'documento',
        ]));

        $second = (new AddSource)->handle(new Request([
            'slug' => $case->slug,
            'title' => 'DIPRES otra vez',
            'url' => $url,
            'kind' => 'documento',
        ]));

        $this->assertFalse($second->isError());
        $this->assertSame(1, $case->fresh()->sources()->count());
    }

    public function test_it_rejects_an_unknown_topic(): void
    {
        $response = (new AddSource)->handle(new Request([
            'slug' => 'no-existe',
            'title' => 'Una fuente',
            'url' => 'https://example.com/nota',
        ]));

        $this->assertTrue($response->isError());
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
            'slug' => 'ley-cholito-recorte',
            'title' => 'Ley Cholito: proponen reducir recursos',
            'procedural_status' => 'en_tramitacion',
            'public_status_label' => 'en_discusion',
            'publication_status' => 'published',
            'published_at' => now(),
            'content_version' => 1,
            'sources_count' => 0,
        ]);
    }
}
