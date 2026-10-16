<?php

namespace Tests\Feature;

use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class InstagramMediaTest extends TestCase
{
    public function test_it_rejects_non_instagram_urls(): void
    {
        $this->getJson('/api/v1/instagram-media?url='.urlencode('https://www.tiktok.com/@x/video/1'))
            ->assertStatus(400);
    }

    public function test_it_returns_a_playable_url_from_ytdlp_metadata(): void
    {
        Process::fake(function (PendingProcess $process) {
            $command = is_array($process->command) ? implode(' ', $process->command) : (string) $process->command;
            $this->assertStringContainsString('-j', $command);

            return Process::result(json_encode([
                'ext' => 'mp4',
                'uploader' => 'cuenta',
                'description' => 'Pie',
                'thumbnail' => 'https://scontent.cdninstagram.com/thumb.jpg',
                'formats' => [
                    ['url' => 'https://scontent.cdninstagram.com/clip.mp4', 'ext' => 'mp4'],
                ],
            ]));
        });

        $this->getJson('/api/v1/instagram-media?url='.urlencode('https://www.instagram.com/reel/AbC123xy/'))
            ->assertOk()
            ->assertJsonPath('kind', 'video')
            ->assertJsonPath('author', 'cuenta')
            ->assertJsonPath('media_url', 'https://scontent.cdninstagram.com/clip.mp4');
    }
}
