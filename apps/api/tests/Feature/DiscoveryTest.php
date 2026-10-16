<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\EditorialCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DiscoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.frontend_url' => 'https://resonaria.cl']);
    }

    public function test_sitemap_lists_static_pages_and_published_topics_only(): void
    {
        $category = $this->category();
        $this->topic($category, 'ley-cholito', 'Ley Cholito', '2026-10-10');
        $this->topic($category, 'borrador', 'Borrador', '2026-10-11', 'draft');

        $response = $this->get('/sitemap.xml');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $xml = $response->getContent();
        $this->assertStringContainsString('<loc>https://resonaria.cl</loc>', $xml);
        $this->assertStringContainsString('<loc>https://resonaria.cl/metodologia</loc>', $xml);
        $this->assertStringContainsString('<loc>https://resonaria.cl/casos/ley-cholito</loc>', $xml);
        $this->assertStringContainsString('<lastmod>2026-10-10</lastmod>', $xml);
        $this->assertStringNotContainsString('borrador', $xml);
    }

    public function test_robots_points_to_the_sitemap(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Sitemap: https://resonaria.cl/sitemap.xml', false)
            ->assertSee('Disallow: /admin', false)
            ->assertSee('Disallow: /api/', false);
    }

    public function test_llms_txt_lists_published_titles_and_stable_slugs(): void
    {
        $category = $this->category();
        $this->topic($category, 'presupuesto-2027-yo-elijo-mi-pc', 'Yo elijo mi PC', '2026-10-11');

        $this->get('/llms.txt')
            ->assertOk()
            ->assertSee('Yo elijo mi PC', false)
            ->assertSee('https://resonaria.cl/casos/presupuesto-2027-yo-elijo-mi-pc', false)
            ->assertSee('llms-full.txt', false);

        $this->get('/llms-full.txt')
            ->assertOk()
            ->assertSee('El slug de la URL es estable', false)
            ->assertSee('https://resonaria.cl/casos/presupuesto-2027-yo-elijo-mi-pc', false);
    }

    public function test_google_verification_file_is_served_when_configured(): void
    {
        config(['app.google_site_verification' => 'abcTOKEN_123']);

        $this->get('/googleabcTOKEN_123.html')
            ->assertOk()
            ->assertSee('google-site-verification: googleabcTOKEN_123.html', false);

        $this->get('/googleother.html')->assertNotFound();
    }

    public function test_seo_description_strips_visual_marks(): void
    {
        $case = $this->topic($this->category(), 'marcas', 'Tema con marcas');
        $case->forceFill([
            'hook' => 'El recorte deja {-$7.793 millones} y **aún no es ley**.',
        ])->save();

        $this->getJson('/api/v1/cases/marcas')
            ->assertOk()
            ->assertJsonPath('data.seo.meta_description', 'El recorte deja $7.793 millones y aún no es ley.')
            ->assertJsonPath('data.seo.canonical_url', 'https://resonaria.cl/casos/marcas');
    }

    public function test_discovery_publish_writes_files(): void
    {
        $this->topic($this->category(), 'sitios-de-memoria', 'Sitios de Memoria');
        $dir = sys_get_temp_dir().'/qp-discovery-'.uniqid();

        $this->artisan('discovery:publish', ['path' => $dir])
            ->assertSuccessful();

        $this->assertFileExists($dir.'/sitemap.xml');
        $this->assertFileExists($dir.'/robots.txt');
        $this->assertFileExists($dir.'/llms.txt');
        $this->assertFileExists($dir.'/llms-full.txt');
        $this->assertStringContainsString('/casos/sitios-de-memoria', File::get($dir.'/sitemap.xml'));

        File::deleteDirectory($dir);
    }

    private function category(): Category
    {
        return Category::query()->create([
            'slug' => 'estado',
            'title' => 'Estado y servicios',
            'lead' => 'Servicios públicos.',
            'sort' => 1,
        ]);
    }

    private function topic(
        Category $category,
        string $slug,
        string $title,
        string $publishedOn = '2026-10-11',
        string $status = 'published',
    ): EditorialCase {
        return EditorialCase::query()->create([
            'category_id' => $category->id,
            'slug' => $slug,
            'title' => $title,
            'hook' => 'Un cambio en trámite.',
            'procedural_status' => 'en_tramitacion',
            'public_status_label' => 'en_discusion',
            'publication_status' => $status,
            'published_at' => $publishedOn,
        ]);
    }
}
