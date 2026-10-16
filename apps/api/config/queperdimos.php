<?php

return [
    'recently_published_days' => (int) env('QP_RECENTLY_PUBLISHED_DAYS', 14),
    'recently_reviewed_days' => (int) env('QP_RECENTLY_REVIEWED_DAYS', 30),
    'movement_reactions_min' => (int) env('QP_MOVEMENT_REACTIONS_MIN', 2),
    'movement_list_limit' => (int) env('QP_MOVEMENT_LIST_LIMIT', 5),
    'analytics' => [
        'public_view_threshold' => (int) env('QP_ANALYTICS_PUBLIC_VIEW_THRESHOLD', 50),
    ],
];
