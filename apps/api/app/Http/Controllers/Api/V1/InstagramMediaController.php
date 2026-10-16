<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\InstagramMediaResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class InstagramMediaController extends Controller
{
    public function show(Request $request, InstagramMediaResolver $resolver): JsonResponse
    {
        $data = $request->validate([
            'url' => ['required', 'url', 'max:2048'],
        ]);

        try {
            return response()->json($resolver->resolve($data['url']));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 400);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 502);
        }
    }
}
