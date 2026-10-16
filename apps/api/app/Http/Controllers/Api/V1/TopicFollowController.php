<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EditorialCase;
use App\Models\TopicFollow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class TopicFollowController extends Controller
{
    public function store(Request $request, string $slug): JsonResponse|Response
    {
        $validator = Validator::make($request->all(), [
            'follower_key' => 'required|string|min:16|max:64',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Clave de seguimiento inválida.'], 422);
        }

        $case = EditorialCase::query()->published()->where('slug', $slug)->firstOrFail();

        TopicFollow::query()->firstOrCreate([
            'case_id' => $case->id,
            'follower_key' => $request->string('follower_key')->toString(),
        ]);

        $count = TopicFollow::query()->where('case_id', $case->id)->count();

        return response()->json([
            'data' => [
                'following' => true,
                'followers_count' => $count,
            ],
        ]);
    }

    public function destroy(Request $request, string $slug): JsonResponse|Response
    {
        $validator = Validator::make($request->all(), [
            'follower_key' => 'required|string|min:16|max:64',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Clave de seguimiento inválida.'], 422);
        }

        $case = EditorialCase::query()->published()->where('slug', $slug)->firstOrFail();

        TopicFollow::query()
            ->where('case_id', $case->id)
            ->where('follower_key', $request->string('follower_key')->toString())
            ->delete();

        $count = TopicFollow::query()->where('case_id', $case->id)->count();

        return response()->json([
            'data' => [
                'following' => false,
                'followers_count' => $count,
            ],
        ]);
    }
}
