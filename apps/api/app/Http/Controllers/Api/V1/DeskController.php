<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EditorialCase;
use App\Models\User;
use App\Support\TopicCoverOptimizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use RuntimeException;

class DeskController extends Controller
{
    public function gate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'gate' => ['required', 'string', 'max:200'],
        ]);

        if (! $this->gateMatches($data['gate'])) {
            abort(404);
        }

        return response()->json(['ok' => true]);
    }

    public function session(Request $request): JsonResponse
    {
        $data = $request->validate([
            'gate' => ['required', 'string', 'max:200'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'max:200'],
        ]);

        $user = User::query()->where('email', $data['email'])->first();

        if (! $this->gateMatches($data['gate']) || ! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'No se pudo entrar.'], 401);
        }

        $token = $user->createToken('desk', ['desk'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'name' => $user->name,
        ]);
    }

    public function cases(Request $request): JsonResponse
    {
        $this->requireDesk($request);

        $cases = EditorialCase::query()
            ->with('category')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (EditorialCase $case) => $this->row($case));

        return response()->json(['data' => $cases]);
    }

    public function update(Request $request, string $slug): JsonResponse
    {
        $this->requireDesk($request);

        $case = EditorialCase::query()->where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'publication_status' => ['sometimes', Rule::in(['draft', 'published'])],
            'title' => ['sometimes', 'string', 'max:180'],
            'hook' => ['sometimes', 'nullable', 'string', 'max:280'],
            'summary' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ]);

        if ($data === []) {
            return response()->json(['message' => 'Nada que guardar.'], 422);
        }

        if (($data['publication_status'] ?? null) === 'published' && $case->published_at === null) {
            $data['published_at'] = now();
        }

        $case->fill($data);
        $case->content_version = (int) $case->content_version + 1;
        $case->save();

        return response()->json(['data' => $this->row($case->fresh('category'))]);
    }

    public function uploadCover(Request $request, string $slug, TopicCoverOptimizer $optimizer): JsonResponse
    {
        $this->requireDesk($request);

        $case = EditorialCase::query()->where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'cover' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:20480'],
        ]);

        $file = $data['cover'];
        $path = $file->getRealPath();
        if ($path === false) {
            return response()->json(['message' => 'No se pudo leer el archivo.'], 422);
        }

        try {
            $result = $optimizer->optimizeFromPath($path, $case->slug);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $case->forceFill([
            'og_image_url' => $result['public_url'],
            'content_version' => (int) $case->content_version + 1,
        ])->save();

        return response()->json([
            'data' => [
                'slug' => $case->slug,
                'og_image_url' => $result['public_url'],
                'width' => $result['width'],
                'height' => $result['height'],
                'bytes' => $result['bytes'],
            ],
        ]);
    }

    private function requireDesk(Request $request): void
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();
        $abilities = $token?->abilities ?? [];

        if (! $user || ! $token || ! in_array('desk', $abilities, true)) {
            abort(401);
        }
    }

    private function gateMatches(string $given): bool
    {
        $expected = (string) config('desk.gate');

        if ($expected === '' || $given === '') {
            return false;
        }

        return hash_equals(hash('sha256', $expected), hash('sha256', $given));
    }

    private function row(EditorialCase $case): array
    {
        return [
            'slug' => $case->slug,
            'title' => $case->title,
            'hook' => $case->hook,
            'summary' => $case->summary,
            'publication_status' => $case->publication_status,
            'public_status_label' => $case->public_status_label,
            'category' => $case->category?->title,
            'updated_at' => $case->updated_at?->toIso8601String(),
        ];
    }
}
