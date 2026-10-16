<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EditorialCase;
use App\Support\ProfilePublicActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CaseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = EditorialCase::query()->published()->with(['category'])->withCount(['reactions', 'claims']);

        if ($areas = $request->query('area')) {
            $areaList = is_array($areas) ? $areas : explode(',', (string) $areas);
            $query->whereHas('category', function ($q) use ($areaList) {
                $q->whereIn('slug', $areaList);
            });
        }

        if ($estados = $request->query('estado')) {
            $estadoList = is_array($estados) ? $estados : explode(',', (string) $estados);
            $query->whereIn('public_status_label', $estadoList);
        }

        if ($tipos = $request->query('tipo')) {
            $tipoList = is_array($tipos) ? $tipos : explode(',', (string) $tipos);
            $query->where(function ($q) use ($tipoList) {
                foreach ($tipoList as $tipo) {
                    $q->orWhereJsonContains('change_types', $tipo);
                }
            });
        }

        if ($piezas = $request->query('pieza')) {
            $piezaList = is_array($piezas) ? $piezas : explode(',', (string) $piezas);
            $query->where(function ($q) use ($piezaList) {
                foreach ($piezaList as $pieza) {
                    if ($pieza === 'seguimiento') {
                        $q->orWhere(fn ($inner) => $inner->decisionFollowUp());
                    }
                    if ($pieza === 'actualidad') {
                        $q->orWhere(fn ($inner) => $inner->actualidad());
                    }
                }
            });
        }

        if ($incluye = $request->query('incluye')) {
            $incluyeList = is_array($incluye) ? $incluye : explode(',', (string) $incluye);
            $query->where(function ($q) use ($incluyeList) {
                foreach ($incluyeList as $criterio) {
                    if ($criterio === 'fuentes') {
                        $q->orWhereHas('sources');
                    }
                    if ($criterio === 'redes') {
                        $q->orWhereHas('reactions');
                    }
                    if ($criterio === 'comparacion') {
                        $q->orWhere(function ($inner) {
                            $inner->whereNotNull('before_text')
                                ->where('before_text', '!=', '')
                                ->whereNotNull('after_text')
                                ->where('after_text', '!=', '');
                        });
                    }
                }
            });
        }

        if ($q = $request->string('q')->trim()->toString()) {
            $like = '%'.$q.'%';
            $query->where(function ($builder) use ($like) {
                $builder->where('title', 'like', $like)
                    ->orWhere('hook', 'like', $like)
                    ->orWhere('summary', 'like', $like)
                    ->orWhere('slug', 'like', $like);
            });
        }

        if ($perfiles = $request->query('perfil')) {
            $perfilList = is_array($perfiles) ? $perfiles : explode(',', (string) $perfiles);
            $caseIds = ProfilePublicActivity::publishedCaseIdsForProfileSlugs($perfilList);
            $query->whereIn('id', $caseIds === [] ? [-1] : $caseIds);
        }

        $perPage = min(max((int) $request->query('per_page', 20), 1), 50);

        $paginator = $query->orderByDesc('published_at')->paginate($perPage);

        return response()->json([
            'data' => $paginator->getCollection()->map->toPublicListArray()->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $case = EditorialCase::query()->published()->with(['category', 'reactions'])->where('slug', $slug)->firstOrFail();

        return response()->json(['data' => $case->toPublicArray()]);
    }
}
