<?php

namespace Tests\Feature;

use App\Models\EditorialCase;
use App\Models\ProfileMention;
use App\Models\PublicProfile;
use App\Models\Reaction;
use App\Support\ProfileImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ProfileScanTest extends TestCase
{
    use RefreshDatabase;

    public function test_scan_confirms_full_name_and_social_handle(): void
    {
        (new ProfileImporter)->import([
            'slug' => 'ministra-demo',
            'display_name' => 'Aurora Chomali',
            'aliases' => [
                ['alias' => 'Aurora Chomali', 'alias_type' => 'nombre_completo'],
                ['alias' => 'ministra de Salud', 'alias_type' => 'cargo_generico'],
            ],
            'roles' => [
                [
                    'title' => 'Ministra de Salud',
                    'organization' => 'Ministerio de Salud',
                    'level' => 'ministro',
                ],
            ],
            'social_accounts' => [
                [
                    'platform' => 'x',
                    'handle' => 'min_demo',
                    'account_kind' => 'institucional',
                    'started_on' => '2025-01-01',
                ],
            ],
        ]);

        $case = EditorialCase::query()->create([
            'slug' => 'ley-demo',
            'title' => 'Ley demo',
            'detail' => 'La ministra de Salud Aurora Chomali declaró continuidad.',
            'procedural_status' => 'vigente',
            'publication_status' => 'published',
            'published_at' => now(),
        ]);

        Reaction::query()->create([
            'case_id' => $case->id,
            'platform' => 'x',
            'url' => 'https://x.com/min_demo/status/1',
            'author_handle' => 'min_demo',
            'author_kind' => 'politico',
        ]);

        Artisan::call('profiles:scan-published');

        $profile = PublicProfile::query()->where('slug', 'ministra-demo')->first();
        $this->assertNotNull($profile);

        $this->assertTrue(ProfileMention::query()
            ->where('profile_id', $profile->id)
            ->where('mentionable_type', EditorialCase::class)
            ->where('confidence', 'confirmed')
            ->exists());

        $this->assertTrue(ProfileMention::query()
            ->where('profile_id', $profile->id)
            ->where('mentionable_type', Reaction::class)
            ->where('confidence', 'confirmed')
            ->exists());
    }

    public function test_scan_does_not_confirm_generic_cargo_without_name(): void
    {
        (new ProfileImporter)->import([
            'slug' => 'ministro-generico',
            'display_name' => 'Ministro Genérico',
            'aliases' => [
                ['alias' => 'el ministro', 'alias_type' => 'cargo_generico'],
            ],
        ]);

        EditorialCase::query()->create([
            'slug' => 'solo-ministro',
            'title' => 'Solo ministro',
            'detail' => 'El ministro dijo algo sin nombre.',
            'procedural_status' => 'vigente',
            'publication_status' => 'published',
            'published_at' => now(),
        ]);

        Artisan::call('profiles:scan-published');

        $this->assertSame(0, ProfileMention::query()->count());
    }
}
