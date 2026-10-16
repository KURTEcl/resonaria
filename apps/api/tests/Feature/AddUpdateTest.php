<?php

namespace Tests\Feature;

use App\Mcp\Tools\AddUpdate;
use App\Models\Category;
use App\Models\EditorialCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Tests\TestCase;

class AddUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_a_follow_up_and_exposes_it_on_the_public_case(): void
    {
        $case = $this->publishedCase();

        $response = (new AddUpdate)->handle(new Request([
            'slug' => $case->slug,
            'label' => 'La comisión pide reponer la glosa.',
            'occurred_on' => '2026-10-11',
            'note' => 'El Congreso abre la discusión de restaurar la partida. Todavía no hay votación.',
            'kind' => 'tramite',
            'direction' => 'reversion',
            'source_url' => 'https://www.camara.cl/legislacion/ejemplo',
            'source_title' => 'Cámara de Diputadas y Diputados',
            'procedural_status' => 'en_tramitacion',
            'public_status_label' => 'en_discusion',
        ]));

        $this->assertFalse($response->isError());
        $this->assertSame(2, $case->fresh()->content_version);
        $this->assertSame('en_tramitacion', $case->fresh()->procedural_status);

        $this->getJson('/api/v1/cases/'.$case->slug)
            ->assertOk()
            ->assertJsonPath('data.milestones.0.label', 'La comisión pide reponer la glosa.')
            ->assertJsonPath('data.milestones.0.direction', 'reversion')
            ->assertJsonPath('data.milestones.0.kind', 'tramite')
            ->assertJsonPath('data.milestones.0.source_title', 'Cámara de Diputadas y Diputados');
    }

    public function test_it_rejects_an_unknown_topic(): void
    {
        $response = (new AddUpdate)->handle(new Request([
            'slug' => 'no-existe',
            'label' => 'Un hito',
        ]));

        $this->assertTrue($response->isError());
    }

    public function test_it_records_occurred_at_for_developing_story_timeline(): void
    {
        $case = $this->publishedCase();

        $response = (new AddUpdate)->handle(new Request([
            'slug' => $case->slug,
            'label' => 'Senapred declara Alerta Roja.',
            'occurred_at' => '2026-10-13T16:54:00-03:00',
            'kind' => 'anuncio',
            'direction' => 'incierto',
        ]));

        $this->assertFalse($response->isError());

        $this->getJson('/api/v1/cases/'.$case->slug)
            ->assertOk()
            ->assertJsonPath('data.milestones.0.occurred_on', '2026-10-13')
            ->assertJsonPath('data.milestones.0.occurred_at', '2026-10-13T16:54:00+00:00');
    }

    public function test_it_rejects_an_invalid_direction(): void
    {
        $case = $this->publishedCase();

        $this->expectException(ValidationException::class);

        (new AddUpdate)->handle(new Request([
            'slug' => $case->slug,
            'label' => 'Un hito',
            'direction' => 'positivo',
        ]));
    }

    private function publishedCase(): EditorialCase
    {
        $category = Category::query()->create([
            'slug' => 'educacion',
            'title' => 'Educación',
            'lead' => 'Cambios en el acceso a la educación y sus apoyos.',
            'sort' => 1,
        ]);

        return EditorialCase::query()->create([
            'category_id' => $category->id,
            'slug' => 'computadores-en-riesgo',
            'title' => 'Computadores escolares en riesgo',
            'procedural_status' => 'en_tramitacion',
            'public_status_label' => 'en_discusion',
            'publication_status' => 'published',
            'published_at' => now(),
            'content_version' => 1,
        ]);
    }
}
