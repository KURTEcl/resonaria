<?php

namespace Tests\Feature;

use App\Mcp\Tools\InspectSocialPost;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Laravel\Mcp\Request;
use Tests\TestCase;

class InspectSocialPostTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'social.ytdlp_bin' => 'yt-dlp',
            'social.ffmpeg_bin' => 'ffmpeg',
            'social.whisper_bin' => 'whisper-cli',
            'social.whisper_model_path' => storage_path('app/whisper-test.bin'),
            'social.openai_key' => null,
            'social.cookies_file' => null,
            'social.max_seconds' => 180,
        ]);
        File::ensureDirectoryExists(storage_path('app'));
        File::put(storage_path('app/whisper-test.bin'), str_repeat('W', 2048));
    }

    protected function tearDown(): void
    {
        File::delete(storage_path('app/whisper-test.bin'));
        parent::tearDown();
    }

    public function test_it_returns_metadata_without_downloading_when_transcribe_is_false(): void
    {
        Http::fake();
        Process::fake(function (PendingProcess $process) {
            $command = $this->processCommand($process);
            if (preg_match('/-version\b/', $command)) {
                return Process::result('yt-dlp 2024.10');
            }

            $this->assertStringContainsString('-j', $command);
            $this->assertStringNotContainsString('-x', $command);

            return Process::result(json_encode([
                'uploader' => 'cuenta_publica',
                'description' => 'Un pie de foto.',
                'duration' => 14,
            ]));
        });

        $response = (new InspectSocialPost)->handle(new Request([
            'url' => 'https://www.instagram.com/reel/AbC123xy/',
            'transcribe' => false,
        ]));

        $payload = json_decode($this->mcpText($response), true);
        $this->assertSame('instagram', $payload['platform']);
        $this->assertSame('cuenta_publica', $payload['author']);
        $this->assertSame('Un pie de foto.', $payload['caption']);
        $this->assertSame(14, $payload['duration_s']);
        $this->assertNull($payload['transcript']);
        $this->assertSame([], $payload['notes']);
        Http::assertNothingSent();
    }

    public function test_it_explains_when_ytdlp_is_missing(): void
    {
        Process::fake([
            '*' => Process::result(output: '', errorOutput: 'not found', exitCode: 127),
        ]);

        $response = (new InspectSocialPost)->handle(new Request([
            'url' => 'https://www.tiktok.com/@cuenta/video/1',
            'transcribe' => false,
        ]));

        $this->assertTrue($response->isError());
        $this->assertStringContainsString('yt-dlp no está instalado', $this->mcpText($response));
    }

    public function test_it_notes_missing_local_whisper_instead_of_transcribing(): void
    {
        config([
            'social.whisper_bin' => 'whisper-cli-ausente',
            'social.whisper_model_path' => storage_path('app/missing-whisper.bin'),
            'social.openai_key' => null,
        ]);

        Process::fake(function (PendingProcess $process) {
            $command = $this->processCommand($process);
            if (str_contains($command, 'whisper-cli-ausente')) {
                return Process::result(output: '', errorOutput: 'not found', exitCode: 127);
            }
            if (preg_match('/-version\b/', $command)) {
                return Process::result(str_contains($command, 'ffmpeg') ? 'ffmpeg version' : 'yt-dlp 2024.10');
            }

            return Process::result(json_encode([
                'uploader' => 'cuenta',
                'description' => 'Pie',
                'duration' => 8,
            ]));
        });

        $response = (new InspectSocialPost)->handle(new Request([
            'url' => 'https://x.com/cuenta/status/1',
        ]));

        $payload = json_decode($this->mcpText($response), true);
        $this->assertSame('Pie', $payload['caption']);
        $this->assertNull($payload['transcript']);
        $this->assertTrue(collect($payload['notes'])->contains(fn ($note) => str_contains($note, 'whisper-cli')));
    }

    public function test_it_transcribes_temporary_audio_and_deletes_it(): void
    {
        Http::fake();

        Process::fake(function (PendingProcess $process) {
            $command = $this->processCommand($process);
            if (preg_match('/-version\b/', $command)) {
                return Process::result('ok 1.0');
            }
            if (str_contains($command, '-j')) {
                return Process::result(json_encode([
                    'uploader' => 'cuenta',
                    'description' => 'Pie',
                    'duration' => 9,
                ]));
            }
            if (str_contains($command, '-otxt') || str_contains($command, 'whisper-cli')) {
                if (preg_match('/-of\s+(\S+)/', $command, $matches) === 1) {
                    File::put($matches[1].'.txt', 'Hola Chile.');
                }

                return Process::result('Hola Chile.');
            }
            if (preg_match('#(/.+?social-inspect/[^/\\s\'\"]+)#', $command, $matches) === 1) {
                $path = $matches[1].'/audio.mp3';
                File::ensureDirectoryExists(dirname($path));
                File::put($path, 'fake-audio');
            }

            return Process::result('');
        });

        $response = (new InspectSocialPost)->handle(new Request([
            'url' => 'https://www.instagram.com/p/PostCode1/',
            'transcribe' => true,
        ]));

        $payload = json_decode($this->mcpText($response), true);
        $this->assertSame('Hola Chile.', $payload['transcript']);
        $this->assertSame([], $payload['notes']);
        $this->assertSame([], File::directories(storage_path('app/tmp/social-inspect')));
        Http::assertNothingSent();
    }

    public function test_doctor_command_reports_missing_binaries(): void
    {
        Process::fake([
            '*' => Process::result(output: '', errorOutput: 'missing', exitCode: 127),
        ]);

        $this->artisan('social:inspect-doctor')
            ->assertFailed()
            ->expectsOutputToContain('yt-dlp no está instalado')
            ->expectsOutputToContain('ffmpeg no está instalado')
            ->expectsOutputToContain('whisper-cli no está instalado');
    }

    private function processCommand(PendingProcess $process): string
    {
        return is_array($process->command) ? implode(' ', $process->command) : (string) $process->command;
    }

    private function mcpText(mixed $response): string
    {
        return (string) $response->content();
    }
}
