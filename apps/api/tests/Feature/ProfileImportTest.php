<?php

namespace Tests\Feature;

use App\Models\PublicProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ProfileImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_command_loads_government_seed(): void
    {
        $path = dirname(__DIR__, 4).'/data/profiles/gobierno-ejecutivo.json';

        Artisan::call('profiles:import', ['path' => $path]);

        $this->assertDatabaseHas('public_profiles', ['slug' => 'jose-antonio-kast', 'status' => 'active']);
        $kast = PublicProfile::query()->where('slug', 'jose-antonio-kast')->first();
        $this->assertNotNull($kast);
        $this->assertTrue($kast->aliases()->where('alias', 'josé antonio kast')->exists());

        $boric = PublicProfile::query()->where('slug', 'gabriel-boric')->first();
        $this->assertSame('archived', $boric?->status);
    }
}
