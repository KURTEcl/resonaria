<?php

namespace Tests\Unit;

use App\Support\SocialPostUrl;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SocialPostUrlTest extends TestCase
{
    #[DataProvider('instagramUrls')]
    public function test_it_parses_instagram_permalink_shapes(string $url, string $type, string $id, bool $share): void
    {
        $parsed = SocialPostUrl::parse($url);

        $this->assertSame('instagram', $parsed['platform']);
        $this->assertSame($type, $parsed['instagram']['type']);
        $this->assertSame($id, $parsed['instagram']['id']);
        $this->assertSame($share, $parsed['instagram']['is_share']);
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string, 3: bool}>
     */
    public static function instagramUrls(): array
    {
        return [
            'p' => ['https://www.instagram.com/p/AbC123xy/', 'p', 'AbC123xy', false],
            'reel' => ['https://www.instagram.com/reel/ReelId99', 'reel', 'ReelId99', false],
            'tv' => ['https://instagram.com/tv/TvShort/', 'tv', 'TvShort', false],
            'reels' => ['https://www.instagram.com/reels/ReelsId/', 'reel', 'ReelsId', false],
            'username reel' => ['https://www.instagram.com/una.cuenta/reel/UserReel1/', 'reel', 'UserReel1', false],
            'username p' => ['https://www.instagram.com/una.cuenta/p/UserPost1/', 'p', 'UserPost1', false],
            'share reel' => ['https://www.instagram.com/share/reel/ShareHash/', 'reel', 'ShareHash', true],
            'share p' => ['https://www.instagram.com/share/p/SharePost/', 'p', 'SharePost', true],
            'instagr.am' => ['https://instagr.am/p/ShortAm/', 'p', 'ShortAm', false],
        ];
    }

    public function test_it_accepts_tiktok_and_x(): void
    {
        $this->assertSame('tiktok', SocialPostUrl::parse('https://www.tiktok.com/@cuenta/video/123')['platform']);
        $this->assertSame('tiktok', SocialPostUrl::parse('https://vm.tiktok.com/ZMabc/')['platform']);
        $this->assertSame('x', SocialPostUrl::parse('https://x.com/cuenta/status/1')['platform']);
        $this->assertSame('x', SocialPostUrl::parse('https://twitter.com/cuenta/status/1')['platform']);
    }

    public function test_it_rejects_http_and_unknown_hosts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        SocialPostUrl::parse('http://www.instagram.com/p/abc/');
    }

    public function test_it_rejects_unrecognized_instagram_paths(): void
    {
        $this->expectException(InvalidArgumentException::class);
        SocialPostUrl::parse('https://www.instagram.com/una.cuenta/');
    }
}
