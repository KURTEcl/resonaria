<?php

namespace Tests\Feature;

use App\Mcp\Tools\AddReaction;
use App\Models\Category;
use App\Models\EditorialCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Mcp\Request;
use Tests\TestCase;

class AddReactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_reddit_and_threads_permalinks(): void
    {
        $case = $this->publishedCase();

        $reddit = (new AddReaction)->handle(new Request([
            'slug' => $case->slug,
            'platform' => 'reddit',
            'url' => 'https://www.reddit.com/r/chile/comments/abc123/presupuesto/',
            'author_handle' => 'cuenta',
        ]));
        $threads = (new AddReaction)->handle(new Request([
            'slug' => $case->slug,
            'platform' => 'threads',
            'url' => 'https://www.threads.com/@cuenta/post/AbC123',
        ]));

        $this->assertFalse($reddit->isError());
        $this->assertFalse($threads->isError());
        $this->assertSame(
            ['reddit', 'threads'],
            $case->fresh()->reactions()->orderBy('id')->pluck('platform')->all(),
        );
    }

    public function test_it_accepts_a_threads_short_link(): void
    {
        $case = $this->publishedCase();

        $response = (new AddReaction)->handle(new Request([
            'slug' => $case->slug,
            'platform' => 'threads',
            'url' => 'https://www.threads.net/t/DDzbnVKx57R',
        ]));

        $this->assertFalse($response->isError());
        $this->assertSame('threads', $case->fresh()->reactions()->first()->platform);
    }

    public function test_it_stores_a_canonical_x_permalink_and_rejects_t_co(): void
    {
        $case = $this->publishedCase();

        $stored = (new AddReaction)->handle(new Request([
            'slug' => $case->slug,
            'platform' => 'x',
            'url' => 'https://twitter.com/TTISantiago/status/2110076692886847843/photo/1',
            'author_handle' => 'TTISantiago',
        ]));
        $short = (new AddReaction)->handle(new Request([
            'slug' => $case->slug,
            'platform' => 'x',
            'url' => 'https://t.co/j8IjS7yLHu',
        ]));

        $this->assertFalse($stored->isError());
        $this->assertTrue($short->isError());
        $this->assertSame(
            'https://x.com/TTISantiago/status/2110076692886847843',
            $case->fresh()->reactions()->first()->url,
        );
    }

    public function test_it_rejects_a_reddit_profile_and_a_mismatched_host(): void
    {
        $case = $this->publishedCase();

        $profile = (new AddReaction)->handle(new Request([
            'slug' => $case->slug,
            'platform' => 'reddit',
            'url' => 'https://www.reddit.com/r/chile/',
        ]));
        $mismatch = (new AddReaction)->handle(new Request([
            'slug' => $case->slug,
            'platform' => 'threads',
            'url' => 'https://www.reddit.com/r/chile/comments/abc123/titulo/',
        ]));

        $this->assertTrue($profile->isError());
        $this->assertTrue($mismatch->isError());
        $this->assertSame(0, $case->fresh()->reactions()->count());
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
