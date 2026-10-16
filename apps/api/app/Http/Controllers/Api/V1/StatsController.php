<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PortfolioStatsService;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    public function portfolio(PortfolioStatsService $stats): JsonResponse
    {
        $payload = $stats->build();

        return response()->json([
            'data' => [
                'summary' => [
                    'total' => $payload['totals']['published'],
                    'en_discusion' => (int) ($payload['by_public_status']['en_discusion'] ?? 0),
                    'with_comparison' => $payload['totals']['with_comparison'],
                    'reviews_overdue' => $payload['totals']['reviews_overdue'],
                    'recently_published' => $payload['totals']['recently_published'],
                ],
                'movement' => $payload['movement'],
            ],
            'meta' => [
                'generated_at' => $payload['generated_at'],
                'methodology' => '/metodologia',
            ],
            'internal' => [
                'totals' => $payload['totals'],
                'by_public_status' => $payload['by_public_status'],
                'by_procedural_status' => $payload['by_procedural_status'],
                'by_evidence_status' => $payload['by_evidence_status'],
                'by_observed_effect' => $payload['by_observed_effect'],
                'by_category' => $payload['by_category'],
                'by_change_type' => $payload['by_change_type'],
            ],
        ]);
    }
}
