<?php

namespace App\Support;

use App\Models\CaseLink;
use App\Models\EditorialCase;

class TopicLinks
{
    public const PATTERN = '/\[\[([a-z0-9][a-z0-9-]*)(?:\|([^\]]+))?\]\]/u';

    /**
     * @return list<string>
     */
    public static function relationKeys(): array
    {
        return array_keys(self::relations());
    }

    /**
     * @return array<string, string>
     */
    public static function relations(): array
    {
        return [
            'contexto' => 'Da contexto',
            'consecuencia' => 'Consecuencia',
            'antecedente' => 'Antecedente',
            'relacionado' => 'Relacionado',
        ];
    }

    public static function markup(string $slug, ?string $anchor = null): string
    {
        $anchor = is_string($anchor) ? trim(str_replace(['[', ']'], '', $anchor)) : '';
        if ($anchor === '' || $anchor === $slug) {
            return '[['.$slug.']]';
        }

        return '[['.$slug.'|'.$anchor.']]';
    }

    /**
     * @return list<string>
     */
    public static function slugsIn(?string $text): array
    {
        if (! is_string($text) || $text === '') {
            return [];
        }

        preg_match_all(self::PATTERN, $text, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }

    public static function syncFromMarkup(EditorialCase $case): void
    {
        $wanted = [];
        foreach (['hook', 'summary', 'what_changes', 'who_affected', 'before_text', 'after_text', 'detail'] as $field) {
            foreach (self::slugsIn(is_string($case->{$field}) ? $case->{$field} : null) as $slug) {
                $wanted[$slug] = true;
            }
        }
        unset($wanted[$case->slug]);

        $slugs = array_keys($wanted);
        $targets = $slugs === []
            ? collect()
            : EditorialCase::query()->published()->whereIn('slug', $slugs)->get()->keyBy('slug');

        $keepTargetIds = [];
        foreach ($slugs as $slug) {
            $target = $targets->get($slug);
            if (! $target instanceof EditorialCase) {
                continue;
            }

            $keepTargetIds[] = $target->id;

            $link = CaseLink::query()->firstOrNew([
                'from_case_id' => $case->id,
                'to_case_id' => $target->id,
            ]);

            if ($link->exists) {
                continue;
            }

            $link->relation = 'relacionado';
            $link->origin = 'markup';
            $link->save();
        }

        $stale = CaseLink::query()
            ->where('from_case_id', $case->id)
            ->where('origin', 'markup');

        if ($keepTargetIds === []) {
            $stale->delete();

            return;
        }

        $stale->whereNotIn('to_case_id', $keepTargetIds)->delete();
    }
}
