<?php

namespace App\Support;

class ClaimChecks
{
    /**
     * @return list<string>
     */
    public static function verdictKeys(): array
    {
        return array_keys(self::verdicts());
    }

    /**
     * @return array<string, string>
     */
    public static function verdicts(): array
    {
        return [
            'se_sostiene' => 'Se sostiene',
            'no_se_sostiene' => 'No se sostiene',
            'a_medias' => 'A medias',
            'sin_respaldo' => 'Falta el dato',
        ];
    }

    public static function key(string $claim): string
    {
        $text = mb_strtolower(trim($claim));
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return hash('sha256', $text);
    }
}
