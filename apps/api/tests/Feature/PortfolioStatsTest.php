<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\EditorialCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_portfolio_stats_returns_summary_and_movement(): void
    {
        $category = Category::query()->create([
            'slug' => 'educacion',
            'title' => 'Educación',
            'lead' => 'Lead',
            'sort' => 1,
        ]);

        $case = EditorialCase::query()->create([
            'category_id' => $category->id,
            'slug' => 'tema-demo',
            'title' => 'Tema demo',
            'hook' => 'Gancho',
            'procedural_status' => 'en_tramitacion',
            'public_status_label' => 'en_discusion',
            'publication_status' => 'published',
            'published_at' => now(),
            'reviewed_at' => now(),
            'before_text' => 'Antes',
            'after_text' => 'Después',
            'sources_count' => 2,
        ]);

        $case->reactions()->createMany([
            ['platform' => 'x', 'url' => 'https://x.com/a/status/1', 'caption' => 'a', 'author_handle' => '@a'],
            ['platform' => 'x', 'url' => 'https://x.com/b/status/2', 'caption' => 'b', 'author_handle' => '@b'],
        ]);

        $this->getJson('/api/v1/stats/portfolio')
            ->assertOk()
            ->assertJsonPath('data.summary.total', 1)
            ->assertJsonPath('data.summary.with_comparison', 1)
            ->assertJsonPath('data.movement.0.slug', 'tema-demo');
    }

    public function test_analytics_view_case_increments_daily_counter(): void
    {
        EditorialCase::query()->create([
            'slug' => 'analytics-demo',
            'title' => 'Analytics',
            'procedural_status' => 'anunciada',
            'publication_status' => 'published',
            'published_at' => now(),
        ]);

        $this->postJson('/api/v1/analytics/events', [
            'event' => 'view_case',
            'slug' => 'analytics-demo',
        ])->assertNoContent();

        $this->assertDatabaseHas('case_analytics_daily', [
            'views' => 1,
        ]);

        $this->assertSame(1, EditorialCase::query()->where('slug', 'analytics-demo')->value('pageviews'));
    }

    public function test_list_includes_pageviews(): void
    {
        $case = EditorialCase::query()->create([
            'slug' => 'vistas-demo',
            'title' => 'Vistas',
            'procedural_status' => 'anunciada',
            'publication_status' => 'published',
            'published_at' => now(),
        ]);
        $case->forceFill(['pageviews' => 42])->save();

        $this->getJson('/api/v1/cases')
            ->assertOk()
            ->assertJsonPath('data.0.pageviews', 42);
    }
}
