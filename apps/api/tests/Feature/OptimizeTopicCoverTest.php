<?php

namespace Tests\Feature;

use App\Models\EditorialCase;
use App\Support\TopicCoverOptimizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class OptimizeTopicCoverTest extends TestCase
{
    use RefreshDatabase;

    private string $publicRoot;

    protected function setUp(): void
    {
        parent::setUp();

        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('GD con WebP no disponible.');
        }

        $this->publicRoot = sys_get_temp_dir().'/qp-covers-'.uniqid();
        File::makeDirectory($this->publicRoot, 0755, true);
        config(['app.web_public_path' => $this->publicRoot]);
        config(['desk.gate' => 'puerta-de-prueba']);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->publicRoot);
        parent::tearDown();
    }

    public function test_optimizer_writes_webp_and_drops_jpeg(): void
    {
        $slug = 'tema-portada';
        $dir = $this->publicRoot.'/casos/'.$slug;
        File::makeDirectory($dir, 0755, true);

        $jpg = imagecreatetruecolor(120, 80);
        $jpegPath = $dir.'/cover.jpg';
        imagejpeg($jpg, $jpegPath, 90);
        imagedestroy($jpg);

        $optimizer = app(TopicCoverOptimizer::class);
        $result = $optimizer->optimizeFromPath($jpegPath, $slug);

        $this->assertSame('/casos/'.$slug.'/cover.webp', $result['public_url']);
        $this->assertFileExists($dir.'/cover.webp');
        $this->assertFileDoesNotExist($jpegPath);
    }

    public function test_desk_upload_cover_sets_og_image_url(): void
    {
        $user = \App\Models\User::factory()->create([
            'email' => 'mesa@queperdimos.local',
            'password' => 'clave-larga-mesa',
        ]);

        EditorialCase::query()->create([
            'slug' => 'tema-upload',
            'title' => 'Tema upload',
            'procedural_status' => 'vigente',
            'publication_status' => 'published',
            'published_at' => now(),
        ]);

        $token = $this->postJson('/api/v1/desk/session', [
            'gate' => 'puerta-de-prueba',
            'email' => 'mesa@queperdimos.local',
            'password' => 'clave-larga-mesa',
        ])->assertOk()->json('token');

        $this->withToken($token)
            ->post('/api/v1/desk/cases/tema-upload/cover', [
                'cover' => UploadedFile::fake()->image('cover.jpg', 640, 360),
            ])
            ->assertOk()
            ->assertJsonPath('data.og_image_url', '/casos/tema-upload/cover.webp');

        $case = EditorialCase::query()->where('slug', 'tema-upload')->first();
        $this->assertSame('/casos/tema-upload/cover.webp', $case->og_image_url);
        $this->assertFileExists($this->publicRoot.'/casos/tema-upload/cover.webp');
    }
}
