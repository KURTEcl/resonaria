<?php

namespace Tests\Feature;

use App\Mcp\Tools\AddClaimCheck;
use App\Models\Category;
use App\Models\EditorialCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Tests\TestCase;

class AddClaimCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_a_circulating_claim_and_its_sourced_reply(): void
    {
        $case = $this->publishedCase();

        $response = (new AddClaimCheck)->handle(new Request([
            'slug' => $case->slug,
            'claim' => 'El recorte elimina el programa completo desde enero.',
            'verdict' => 'no_se_sostiene',
            'response' => 'El proyecto **baja el presupuesto**, no cierra el programa. El texto sigue vigente en 2027.',
            'heard_on' => 'Comentarios en redes',
            'source_url' => 'https://www.dipres.gob.cl/597/w3-multipropertyvalues-15168-38403.html',
            'source_title' => 'DIPRES — proyecto de presupuesto 2027',
            'source_kind' => 'documento',
        ]));

        $this->assertFalse($response->isError());
        $this->assertSame(1, $case->fresh()->claims()->count());
        $this->assertSame(1, $case->fresh()->sources_count);
        $this->assertSame(2, $case->fresh()->content_version);

        $this->getJson('/api/v1/cases/'.$case->slug)
            ->assertOk()
            ->assertJsonPath('data.claims_count', 1)
            ->assertJsonPath('data.claims.0.verdict', 'no_se_sostiene')
            ->assertJsonPath('data.claims.0.heard_on', 'Comentarios en redes')
            ->assertJsonPath('data.sources.0.is_primary', true);

        $this->getJson('/api/v1/cases')
            ->assertOk()
            ->assertJsonPath('data.0.claims_count', 1);
    }

    public function test_it_skips_the_same_claim_with_different_spacing(): void
    {
        $case = $this->publishedCase();
        $payload = [
            'slug' => $case->slug,
            'claim' => 'Se acaba el tratamiento.',
            'verdict' => 'sin_respaldo',
            'response' => 'No hay un documento que diga que se interrumpe el tratamiento.',
        ];

        (new AddClaimCheck)->handle(new Request($payload));

        $second = (new AddClaimCheck)->handle(new Request([
            ...$payload,
            'claim' => '  se acaba   el tratamiento. ',
        ]));

        $this->assertFalse($second->isError());
        $this->assertSame(1, $case->fresh()->claims()->count());
        $this->assertSame(2, $case->fresh()->content_version);
    }

    public function test_it_requires_a_source_when_the_verdict_is_not_missing_data(): void
    {
        $this->publishedCase();

        $this->expectException(ValidationException::class);

        (new AddClaimCheck)->handle(new Request([
            'slug' => 'ley-cholito-recorte',
            'claim' => 'Baja un 86%.',
            'verdict' => 'se_sostiene',
            'response' => 'La cifra sale de la comparación 2026-2027.',
        ]));
    }

    public function test_it_rejects_an_unknown_topic(): void
    {
        $response = (new AddClaimCheck)->handle(new Request([
            'slug' => 'no-existe',
            'claim' => 'Una idea que circula.',
            'verdict' => 'sin_respaldo',
            'response' => 'No hay dato para afirmarlo.',
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
