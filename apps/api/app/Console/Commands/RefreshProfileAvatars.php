<?php

namespace App\Console\Commands;

use App\Models\PublicProfile;
use App\Support\ProfileAvatarService;
use Illuminate\Console\Command;

class RefreshProfileAvatars extends Command
{
    protected $signature = 'profiles:refresh-avatars {--slug= : Solo un perfil}';

    protected $description = 'Descarga la foto de perfil desde X y la guarda en almacenamiento local';

    public function handle(ProfileAvatarService $avatars): int
    {
        $query = PublicProfile::query()->where('status', 'active');
        if ($slug = $this->option('slug')) {
            $query->where('slug', $slug);
        }

        $ok = 0;
        $fail = 0;

        $query->orderBy('slug')->each(function (PublicProfile $profile) use ($avatars, &$ok, &$fail) {
            if ($avatars->refresh($profile)) {
                $ok++;
                $this->line('✓ '.$profile->slug);
            } else {
                $fail++;
                $this->line('✗ '.$profile->slug);
            }
        });

        $this->info("Actualizados: {$ok}. Sin cambio o error: {$fail}.");

        return self::SUCCESS;
    }
}
