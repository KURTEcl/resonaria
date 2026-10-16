<?php

namespace App\Support;

class PlainText
{
    /**
     * El signo $ solo abre una cifra en pesos ({-$1.896 millones}).
     * Delante de una palabra es un error de redacción ({-$menos} → {-menos}).
     */
    public static function normalizeMarks(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return $text;
        }

        return preg_replace('/(\{[+-])\$(?=\p{L})/u', '$1', $text) ?? $text;
    }

    public static function fromMarked(?string $text): string
    {
        if ($text === null || trim($text) === '') {
            return '';
        }

        $plain = preg_replace('/\{\+([^}]+)\}|\{-([^}]+)\}|\*\*([^*]+)\*\*/u', '$1$2$3', $text) ?? $text;
        $plain = preg_replace_callback(TopicLinks::PATTERN, function (array $match) {
            return (isset($match[2]) && $match[2] !== '') ? $match[2] : $match[1];
        }, $plain) ?? $plain;
        $plain = preg_replace('/\[(\d+)\]/u', '', $plain) ?? $plain;
        $plain = preg_replace('/^##\s+/um', '', $plain) ?? $plain;
        $plain = preg_replace('/\s+/u', ' ', $plain) ?? $plain;

        return trim($plain);
    }

    public static function excerpt(?string $text, int $max = 220): ?string
    {
        $plain = self::fromMarked($text);
        if ($plain === '') {
            return null;
        }

        if (mb_strlen($plain) <= $max) {
            return $plain;
        }

        return rtrim(mb_substr($plain, 0, $max - 1)).'…';
    }
}
