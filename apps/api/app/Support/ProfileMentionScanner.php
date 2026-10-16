<?php

namespace App\Support;

use App\Models\CaseMilestone;
use App\Models\CaseSource;
use App\Models\EditorialCase;
use App\Models\ProfileMention;
use App\Models\PublicProfile;
use App\Models\Reaction;
use Carbon\CarbonInterface;

class ProfileMentionScanner
{
    /** @var list<array{profile_id: int, alias: string, alias_type: string, organization: ?string}> */
    private array $aliasIndex = [];

    public function __construct()
    {
        $this->buildAliasIndex();
    }

    public function scanPublished(bool $dryRun = false): array
    {
        $stats = ['created' => 0, 'skipped' => 0, 'suggested' => 0, 'confirmed' => 0];

        EditorialCase::query()
            ->published()
            ->with(['sources', 'milestones', 'reactions'])
            ->orderBy('id')
            ->each(function (EditorialCase $case) use ($dryRun, &$stats) {
                $referenceDate = $case->published_at ?? now();
                $text = $this->caseText($case);
                $this->applyTextMatch($case, $text, $referenceDate, $dryRun, $stats, 'mencionado');

                foreach ($case->sources as $source) {
                    $sourceText = trim($source->title.' '.($source->url ?? ''));
                    $this->applyTextMatch($source, $sourceText, $referenceDate, $dryRun, $stats, 'mencionado');
                }

                foreach ($case->milestones as $milestone) {
                    $milestoneText = trim($milestone->label.' '.($milestone->note ?? ''));
                    $date = $milestone->occurred_at ?? $milestone->occurred_on ?? $referenceDate;
                    $this->applyTextMatch($milestone, $milestoneText, $date, $dryRun, $stats, 'mencionado');
                }

                foreach ($case->reactions as $reaction) {
                    $this->applyReactionMatch($reaction, $referenceDate, $dryRun, $stats);
                }
            });

        return $stats;
    }

    private function buildAliasIndex(): void
    {
        $profiles = PublicProfile::query()
            ->where('status', 'active')
            ->with(['aliases', 'roles' => fn ($q) => $q->whereNull('ended_on')])
            ->get();

        foreach ($profiles as $profile) {
            $organization = $profile->roles->first()?->organization;
            foreach ($profile->aliases as $alias) {
                $this->aliasIndex[] = [
                    'profile_id' => $profile->id,
                    'alias' => $alias->alias,
                    'alias_type' => $alias->alias_type,
                    'organization' => $organization ? mb_strtolower($organization) : null,
                ];
            }
        }

        $typeRank = ['nombre_completo' => 0, 'apellido' => 1, 'cuenta' => 2, 'cargo_generico' => 3];
        usort($this->aliasIndex, function ($a, $b) use ($typeRank) {
            $ra = $typeRank[$a['alias_type']] ?? 9;
            $rb = $typeRank[$b['alias_type']] ?? 9;
            if ($ra !== $rb) {
                return $ra <=> $rb;
            }

            return mb_strlen($b['alias']) <=> mb_strlen($a['alias']);
        });
    }

    private function caseText(EditorialCase $case): string
    {
        $parts = [
            $case->title,
            $case->hook,
            $case->summary,
            $case->what_changes,
            $case->who_affected,
            $case->before_text,
            $case->after_text,
            $case->detail,
        ];

        return PlainText::fromMarked(implode("\n", array_filter($parts)));
    }

    /**
     * @param  array<string, int>  $stats
     */
    private function applyTextMatch(
        EditorialCase|CaseSource|CaseMilestone $model,
        string $text,
        CarbonInterface $referenceDate,
        bool $dryRun,
        array &$stats,
        string $defaultRole,
    ): void {
        if ($text === '') {
            return;
        }

        $haystack = mb_strtolower($text);

        foreach ($this->aliasIndex as $entry) {
            if ($entry['alias_type'] === 'cargo_generico' && ! $this->genericCargoMatches($haystack, $entry)) {
                continue;
            }

            if (! preg_match(ProfileText::wordBoundaryPattern($entry['alias']), $haystack)) {
                continue;
            }

            $confidence = config('profiles.scan_auto_confirm', true) ? 'confirmed' : (
                in_array($entry['alias_type'], ['nombre_completo', 'apellido'], true) ? 'confirmed' : 'suggested'
            );

            $excerpt = PlainText::excerpt($text, 200);
            $this->storeMention($entry['profile_id'], $model, $defaultRole, 'scan', $confidence, $excerpt, $dryRun, $stats);
        }
    }

    /**
     * @param  array{profile_id: int, alias: string, alias_type: string, organization: ?string}  $entry
     */
    private function genericCargoMatches(string $haystack, array $entry): bool
    {
        if (! preg_match(ProfileText::wordBoundaryPattern($entry['alias']), $haystack)) {
            return false;
        }

        if ($entry['organization'] && str_contains($haystack, $entry['organization'])) {
            return true;
        }

        $profile = PublicProfile::query()->with('aliases')->find($entry['profile_id']);
        if (! $profile) {
            return false;
        }

        foreach ($profile->aliases as $alias) {
            if (in_array($alias->alias_type, ['nombre_completo', 'apellido'], true)
                && preg_match(ProfileText::wordBoundaryPattern($alias->alias), $haystack)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, int>  $stats
     */
    private function applyReactionMatch(Reaction $reaction, CarbonInterface $fallbackDate, bool $dryRun, array &$stats): void
    {
        $handle = $reaction->author_handle ? ProfileText::normalizeHandle($reaction->author_handle) : '';
        if ($handle === '') {
            if ($reaction->author_kind === 'politico') {
                $stats['skipped']++;

                return;
            }

            return;
        }

        $date = $reaction->created_at ?? $fallbackDate;
        $profileId = ProfileSocialResolver::resolveProfileId($reaction->platform, $handle, $date);

        if (! $profileId) {
            return;
        }

        $excerpt = PlainText::excerpt($reaction->caption ?? '', 200);
        $this->storeMention($profileId, $reaction, 'mencionado', 'scan', 'confirmed', $excerpt, $dryRun, $stats);
    }

    /**
     * @param  array<string, int>  $stats
     */
    private function storeMention(
        int $profileId,
        EditorialCase|CaseSource|CaseMilestone|Reaction $model,
        string $role,
        string $origin,
        string $confidence,
        ?string $excerpt,
        bool $dryRun,
        array &$stats,
    ): void {
        $exists = ProfileMention::query()
            ->where('profile_id', $profileId)
            ->where('mentionable_type', $model::class)
            ->where('mentionable_id', $model->id)
            ->exists();

        if ($exists) {
            if (! $dryRun && $confidence === 'confirmed') {
                ProfileMention::query()
                    ->where('profile_id', $profileId)
                    ->where('mentionable_type', $model::class)
                    ->where('mentionable_id', $model->id)
                    ->where('confidence', 'suggested')
                    ->update(['confidence' => 'confirmed']);
            }
            $stats['skipped']++;

            return;
        }

        if ($dryRun) {
            $stats['created']++;
            if ($confidence === 'confirmed') {
                $stats['confirmed']++;
            } else {
                $stats['suggested']++;
            }

            return;
        }

        ProfileMention::query()->create([
            'profile_id' => $profileId,
            'mentionable_type' => $model::class,
            'mentionable_id' => $model->id,
            'role' => $role,
            'origin' => $origin,
            'confidence' => $confidence,
            'excerpt' => $excerpt,
        ]);

        $stats['created']++;
        if ($confidence === 'confirmed') {
            $stats['confirmed']++;
        } else {
            $stats['suggested']++;
        }
    }
}
