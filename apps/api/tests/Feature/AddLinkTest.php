<?php

namespace Tests\Feature;

use App\Mcp\Tools\AddLink;
use App\Mcp\Tools\CreateTopic;
use App\Mcp\Tools\UpdateTopic;
use App\Models\Category;
use App\Models\EditorialCase;
use App\Support\PlainText;
use App\Support\TopicLinks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Mcp\Request;
use Tests\TestCase;

class AddLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_associates_two_published_topics_and_returns_wiki_markup(): void
    {
        [$storm, $soil] = $this->publishedPair();

        $response = (new AddLink)->handle(new Request([
            'slug' => $storm->slug,
            'to_slug' => $soil->slug,
            'relation' => 'contexto',
            'note' => 'El recorte a estudios de suelo agrava el riesgo del temporal.',
            'anchor' => 'recorte a estudios de suelo',
        ]));

        $this->assertFalse($response->isError());
        $this->assertSame(2, $storm->fresh()->content_version);
        $this->assertSame(1, $storm->fresh()->outgoingLinks()->count());

        $this->getJson('/api/v1/cases/'.$storm->slug)
            ->assertOk()
            ->assertJsonPath('data.links.0.slug', $soil->slug)
            ->assertJsonPath('data.links.0.relation', 'contexto')
            ->assertJsonPath('data.links.0.note', 'El recorte a estudios de suelo agrava el riesgo del temporal.');

        $this->getJson('/api/v1/cases/'.$soil->slug)
            ->assertOk()
            ->assertJsonPath('data.linked_from.0.slug', $storm->slug)
            ->assertJsonPath('data.linked_from.0.relation', 'contexto');
    }

    public function test_it_skips_the_same_pair(): void
    {
        [$storm, $soil] = $this->publishedPair();

        (new AddLink)->handle(new Request([
            'slug' => $storm->slug,
            'to_slug' => $soil->slug,
            'relation' => 'relacionado',
        ]));

        $second = (new AddLink)->handle(new Request([
            'slug' => $storm->slug,
            'to_slug' => $soil->slug,
            'relation' => 'relacionado',
        ]));

        $this->assertFalse($second->isError());
        $this->assertSame(1, $storm->fresh()->outgoingLinks()->count());
        $this->assertSame(2, $storm->fresh()->content_version);
    }

    public function test_it_rejects_a_self_link_and_an_unknown_target(): void
    {
        [$storm] = $this->publishedPair();

        $self = (new AddLink)->handle(new Request([
            'slug' => $storm->slug,
            'to_slug' => $storm->slug,
        ]));
        $this->assertTrue($self->isError());

        $missing = (new AddLink)->handle(new Request([
            'slug' => $storm->slug,
            'to_slug' => 'no-existe',
        ]));
        $this->assertTrue($missing->isError());
    }

    public function test_it_harvests_wiki_markup_from_detail_and_drops_it_when_removed(): void
    {
        [$storm, $soil] = $this->publishedPair();

        (new UpdateTopic)->handle(new Request([
            'slug' => $storm->slug,
            'detail' => '## Qué está en juego'."\n\n".'El [[estudios-de-suelo-presupuesto-2027|recorte a estudios de suelo]] deja menos mapeo de cauces.',
        ]));

        $this->assertSame(1, $storm->fresh()->outgoingLinks()->count());
        $this->assertSame('markup', $storm->fresh()->outgoingLinks()->first()->origin);
        $this->assertSame($soil->id, $storm->fresh()->outgoingLinks()->first()->to_case_id);

        (new UpdateTopic)->handle(new Request([
            'slug' => $storm->slug,
            'detail' => '## Qué está en juego'."\n\n".'El temporal expuso cauces sin mapeo reciente.',
        ]));

        $this->assertSame(0, $storm->fresh()->outgoingLinks()->count());
    }

    public function test_tool_links_survive_when_markup_is_removed(): void
    {
        [$storm, $soil] = $this->publishedPair();

        (new AddLink)->handle(new Request([
            'slug' => $storm->slug,
            'to_slug' => $soil->slug,
        ]));

        (new UpdateTopic)->handle(new Request([
            'slug' => $storm->slug,
            'detail' => 'Sin enlace interno.',
        ]));

        $this->assertSame(1, $storm->fresh()->outgoingLinks()->count());
        $this->assertSame('tool', $storm->fresh()->outgoingLinks()->first()->origin);
    }

    public function test_create_topic_harvests_wiki_markup(): void
    {
        $this->publishedPair();

        (new CreateTopic)->handle(new Request([
            'category_slug' => 'territorio',
            'title' => 'Temporal en el centro',
            'detail' => 'El [[estudios-de-suelo-presupuesto-2027|recorte a estudios de suelo]] deja menos mapeo.',
        ]));

        $created = EditorialCase::query()->where('slug', 'temporal-en-el-centro')->first();
        $this->assertNotNull($created);
        $this->assertSame(1, $created->outgoingLinks()->count());
        $link = $created->outgoingLinks()->with('toCase')->first();
        $this->assertSame('estudios-de-suelo-presupuesto-2027', $link?->toCase?->slug);
    }

    public function test_markup_helper_and_slug_parser(): void
    {
        $this->assertSame(
            '[[estudios-de-suelo-presupuesto-2027|recorte a estudios de suelo]]',
            TopicLinks::markup('estudios-de-suelo-presupuesto-2027', 'recorte a estudios de suelo'),
        );
        $this->assertSame(
            ['estudios-de-suelo-presupuesto-2027'],
            TopicLinks::slugsIn('El [[estudios-de-suelo-presupuesto-2027|recorte]] y otra cifra [1].'),
        );
        $this->assertSame(
            'El recorte a estudios de suelo agrava el riesgo.',
            PlainText::fromMarked('El [[estudios-de-suelo-presupuesto-2027|recorte a estudios de suelo]] agrava el riesgo.'),
        );
    }

    /**
     * @return array{0: EditorialCase, 1: EditorialCase}
     */
    private function publishedPair(): array
    {
        $category = Category::query()->create([
            'slug' => 'territorio',
            'title' => 'Territorio',
            'lead' => 'Decisiones sobre suelo, vivienda y riesgo.',
            'sort' => 1,
        ]);

        $storm = EditorialCase::query()->create([
            'category_id' => $category->id,
            'slug' => 'temporal-zona-centro',
            'title' => 'El temporal deja poblaciones sin mapeo de cauces',
            'hook' => 'Las lluvias encontraron cauces sin estudio reciente.',
            'procedural_status' => 'en_tramitacion',
            'public_status_label' => 'en_discusion',
            'publication_status' => 'published',
            'published_at' => now(),
            'content_version' => 1,
        ]);

        $soil = EditorialCase::query()->create([
            'category_id' => $category->id,
            'slug' => 'estudios-de-suelo-presupuesto-2027',
            'title' => 'Proponen recortar estudios de suelo',
            'hook' => 'El proyecto deja en duda el mapeo de cauces.',
            'procedural_status' => 'en_tramitacion',
            'public_status_label' => 'en_discusion',
            'publication_status' => 'published',
            'published_at' => now(),
            'content_version' => 1,
        ]);

        return [$storm, $soil];
    }
}
