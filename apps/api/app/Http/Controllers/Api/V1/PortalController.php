<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PortalController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json([
            'name' => config('portal.name'),
            'tagline' => config('portal.tagline'),
            'description' => config('portal.description'),
            'locale' => config('portal.locale'),
            'github_url' => config('portal.github_url'),
            'support_url' => config('portal.support_url'),
            'social' => [
                'instagram' => config('portal.social_instagram') ?: null,
                'x' => config('portal.social_x') ?: null,
            ],
            'content_types' => config('portal.content_types'),
            'editorial_tone' => config('portal.editorial_tone'),
        ]);
    }
}
