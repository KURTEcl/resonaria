<?php

namespace Tests\Feature;

use App\Mcp\Tools\SetTopicCover;
use App\Models\EditorialCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Laravel\Mcp\Request;
use Tests\TestCase;

class SetTopicCoverTest extends TestCase
{
    use RefreshDatabase;

    private string $publicRoot;

    protected function setUp(): void
    {
        parent::setUp();

        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('GD con WebP no disponible.');
        }

        $this->publicRoot = sys_get_temp_dir().'/qp-mcp-cover-'.uniqid();
        File::makeDirectory($this->publicRoot, 0755, true);
        File::put($this->publicRoot.'/200.html', '<!DOCTYPE html><html><body>spa</body></html>');
        config(['app.web_public_path' => $this->publicRoot]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->publicRoot);
        parent::tearDown();
    }

    public function test_set_topic_cover_from_base64(): void
    {
        $case = $this->publishedCase('tema-portada-mcp');

        $jpg = imagecreatetruecolor(200, 120);
        ob_start();
        imagejpeg($jpg, null, 90);
        $binary = ob_get_clean();
        imagedestroy($jpg);

        $response = (new SetTopicCover)->handle(new Request([
            'slug' => $case->slug,
            'image_base64' => base64_encode($binary),
        ]));

        $this->assertFalse($response->isError());
        $this->assertSame('/casos/tema-portada-mcp/cover.webp', $case->fresh()->og_image_url);
        $this->assertFileExists($this->publicRoot.'/casos/tema-portada-mcp/cover.webp');
        $this->assertFileExists($this->publicRoot.'/casos/tema-portada-mcp/index.html');
    }

    public function test_set_topic_cover_from_url(): void
    {
        $case = $this->publishedCase('tema-url-mcp');

        $jpg = imagecreatetruecolor(160, 90);
        ob_start();
        imagejpeg($jpg, null, 90);
        $binary = ob_get_clean();
        imagedestroy($jpg);

        Http::fake([
            'https://cdn.example.test/hero.jpg' => Http::response($binary, 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $response = (new SetTopicCover)->handle(new Request([
            'slug' => $case->slug,
            'image_url' => 'https://cdn.example.test/hero.jpg',
        ]));

        $this->assertFalse($response->isError());
        $this->assertFileExists($this->publicRoot.'/casos/tema-url-mcp/cover.webp');
    }

    public function test_rejects_unknown_slug(): void
    {
        $response = (new SetTopicCover)->handle(new Request([
            'slug' => 'no-existe',
            'image_base64' => base64_encode('not-an-image'),
        ]));

        $this->assertTrue($response->isError());
    }

    private function publishedCase(string $slug): EditorialCase
    {
        return EditorialCase::query()->create([
            'slug' => $slug,
            'title' => 'Tema '.$slug,
            'procedural_status' => 'vigente',
            'publication_status' => 'published',
            'published_at' => now(),
        ]);
    }
}
