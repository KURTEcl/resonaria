<?php

namespace App\Support;

class ReactionAuthorKinds
{
    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(config('reaction_author_kinds', []));
    }

    public static function label(?string $key): string
    {
        if (! $key) {
            return config('reaction_author_kinds.sin_clasificar', 'Sin clasificar');
        }

        return config("reaction_author_kinds.{$key}") ?? $key;
    }
}
