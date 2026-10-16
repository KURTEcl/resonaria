<?php

namespace App\Services;

use App\Models\CaseAnalyticsDaily;
use App\Models\EditorialCase;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PortfolioStatsService
{
    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $cases = EditorialCase::query()
            ->published()
            ->with(['category', 'milestones'])
            ->withCount('reactions')
            ->get();

        $today = Carbon::today();

        $byPublicStatus = $this->countBy($cases, 'public_status_label');
        $byProcedural = $this->countBy($cases, 'procedural_status');
        $byEvidence = $this->countBy($cases, 'evidence_status');
        $byEffect = $this->countBy($cases, 'observed_effect');
        $byCategory = $cases
            ->groupBy(fn (EditorialCase $c) => $c->category?->slug ?? 'sin_categoria')
            ->map->count()
            ->sortDesc()
            ->all();

        $changeTypeCounts = [];
        foreach ($cases as $case) {
            foreach ($case->change_types ?? [] as $type) {
                $changeTypeCounts[$type] = ($changeTypeCounts[$type] ?? 0) + 1;
            }
        }
        ksort($changeTypeCounts);

        $withComparison = $cases->filter(fn (EditorialCase $c) => filled($c->before_text) && filled($c->after_text))->count();
        $evidenceKnown = $cases->filter(fn (EditorialCase $c) => in_array($c->evidence_status, ['partial', 'documented', 'complete'], true))->count();
        $reviewsOverdue = $cases->filter(fn (EditorialCase $c) => $c->next_review_at && $c->next_review_at->lt($today))->count();

        $publishedDays = (int) config('queperdimos.recently_published_days', 14);
        $reviewedDays = (int) config('queperdimos.recently_reviewed_days', 30);
        $recentlyPublished = $cases->filter(
            fn (EditorialCase $c) => $c->published_at && $c->published_at->gte($today->copy()->subDays($publishedDays))
        )->count();

        $sourcesTotal = (int) $cases->sum('sources_count');
        $reactionsTotal = (int) $cases->sum('reactions_count');

        $movement = $this->buildMovementList($cases, $today);

        return [
            'generated_at' => now()->toIso8601String(),
            'totals' => [
                'published' => $cases->count(),
                'with_comparison' => $withComparison,
                'evidence_partial_or_better' => $evidenceKnown,
                'reviews_overdue' => $reviewsOverdue,
                'recently_published' => $recentlyPublished,
                'sources_count_sum' => $sourcesTotal,
                'reactions_count_sum' => $reactionsTotal,
            ],
            'by_public_status' => $byPublicStatus,
            'by_procedural_status' => $byProcedural,
            'by_evidence_status' => $byEvidence,
            'by_observed_effect' => $byEffect,
            'by_category' => $byCategory,
            'by_change_type' => $changeTypeCounts,
            'movement' => $movement,
        ];
    }

    /**
     * @param  Collection<int, EditorialCase>  $cases
     * @return list<array{slug: string, title: string, reasons: list<string>, score: int}>
     */
    private function buildMovementList(Collection $cases, Carbon $today): array
    {
        $publishedDays = (int) config('queperdimos.recently_published_days', 14);
        $reviewedDays = (int) config('queperdimos.recently_reviewed_days', 30);
        $reactionsMin = (int) config('queperdimos.movement_reactions_min', 2);
        $limit = (int) config('queperdimos.movement_list_limit', 5);
        $viewThreshold = (int) config('queperdimos.analytics.public_view_threshold', 50);
        $viewWindowStart = $today->copy()->subDays(7);

        $caseIds = $cases->pluck('id');
        $viewsByCase = CaseAnalyticsDaily::query()
            ->whereIn('case_id', $caseIds)
            ->where('metric_date', '>=', $viewWindowStart)
            ->selectRaw('case_id, SUM(views) as total_views')
            ->groupBy('case_id')
            ->pluck('total_views', 'case_id');

        $items = [];

        foreach ($cases as $case) {
            $reasons = [];

            if ($case->published_at && $case->published_at->gte($today->copy()->subDays($publishedDays))) {
                $reasons[] = 'Publicado recientemente';
            }
            if ($case->reviewed_at && $case->reviewed_at->gte($today->copy()->subDays($reviewedDays))) {
                $reasons[] = 'Revisión editorial reciente';
            }
            if ((int) ($case->reactions_count ?? 0) >= $reactionsMin) {
                $reasons[] = (int) $case->reactions_count.' piezas en redes';
            }

            $recentMilestone = $case->milestones->first(
                fn ($milestone) => $milestone->occurred_on->gte($today->copy()->subDays($reviewedDays)),
            );
            if ($recentMilestone) {
                $reasons[] = 'Hito: '.$recentMilestone->label;
            }

            $views = (int) ($viewsByCase[$case->id] ?? 0);
            if ($views >= $viewThreshold) {
                $reasons[] = 'Alta lectura esta semana';
            }

            if ($reasons === []) {
                continue;
            }

            $items[] = [
                'slug' => $case->slug,
                'title' => $case->title,
                'reasons' => $reasons,
                'score' => count($reasons),
                'last_activity' => $this->lastActivityAt($case)?->toIso8601String(),
            ];
        }

        usort($items, function (array $a, array $b) {
            if ($a['score'] !== $b['score']) {
                return $b['score'] <=> $a['score'];
            }

            return strcmp($b['last_activity'] ?? '', $a['last_activity'] ?? '');
        });

        return array_map(
            fn (array $row) => [
                'slug' => $row['slug'],
                'title' => $row['title'],
                'reasons' => $row['reasons'],
            ],
            array_slice($items, 0, $limit),
        );
    }

    private function lastActivityAt(EditorialCase $case): ?Carbon
    {
        $dates = array_filter([
            $case->published_at,
            $case->reviewed_at,
            $case->updated_at,
        ]);

        if ($dates === []) {
            return null;
        }

        return collect($dates)->max();
    }

    /**
     * @param  Collection<int, EditorialCase>  $cases
     * @return array<string, int>
     */
    private function countBy(Collection $cases, string $attribute): array
    {
        return $cases
            ->groupBy(fn (EditorialCase $c) => $c->{$attribute} ?? 'unknown')
            ->map->count()
            ->sortDesc()
            ->all();
    }
}
