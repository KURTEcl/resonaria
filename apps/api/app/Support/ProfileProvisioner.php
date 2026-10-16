<?php

namespace App\Support;

use App\Models\PublicProfile;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ProfileProvisioner
{
    /**
     * Perfil activo listo para vincular a una nota. Crea o reactiva si hace falta.
     *
     * @param  list<string|array{alias: string, alias_type?: string}>  $extraAliases
     */
    public function ensureActiveForMention(
        string $slug,
        ?string $displayName = null,
        ?string $kind = null,
        array $extraAliases = [],
    ): PublicProfile {
        $slug = Str::slug($slug);
        if ($slug === '') {
            throw new InvalidArgumentException('Slug de perfil inválido.');
        }

        $existing = PublicProfile::query()->where('slug', $slug)->first();
        if ($existing) {
            if ($existing->status !== 'active') {
                $existing->forceFill(['status' => 'active'])->save();
            }

            return $existing->fresh(['aliases', 'roles', 'socialAccounts']);
        }

        $name = trim((string) $displayName);
        if ($name === '') {
            $name = Str::title(str_replace(['-', '_'], ' ', $slug));
        }

        $aliases = [['alias' => $name, 'alias_type' => 'nombre_completo']];
        foreach ($extraAliases as $row) {
            if (is_string($row) && $row !== '') {
                $aliases[] = ['alias' => $row, 'alias_type' => 'nombre_completo'];
            } elseif (is_array($row) && isset($row['alias'])) {
                $aliases[] = [
                    'alias' => (string) $row['alias'],
                    'alias_type' => $row['alias_type'] ?? 'nombre_completo',
                ];
            }
        }

        return (new ProfileImporter)->import([
            'slug' => $slug,
            'display_name' => $name,
            'kind' => $kind ?? 'otro',
            'status' => 'active',
            'aliases' => $aliases,
        ]);
    }
}
