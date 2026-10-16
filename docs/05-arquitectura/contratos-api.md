# Contratos API (REST)

Contrato canónico para portal, admin y agentes. Publicar **OpenAPI 3** en `apps/api/storage/api/openapi.yaml` (o ruta `/api/openapi`).

## Versión y formato

- Base: `/api/v1`
- JSON UTF-8
- Paginación: `page`, `per_page` (máx. 50)
- Errores: `{ "message", "errors?", "code?" }`
- Concurrencia: header `If-Match` con `content_version` en escrituras

## Recursos públicos (solo `published`)

| Método | Ruta | Descripción |
| --- | --- | --- |
| GET | `/cases` | Catálogo; query `area[]`, `estado[]`, `tipo[]`, `q` (ver [busqueda-y-filtros.md](busqueda-y-filtros.md)) |
| GET | `/cases/{slug}` | Ficha pública + relaciones (`sources`, `milestones`, `reactions`) |
| GET | `/stats/portfolio` | Agregados de cartera y lista «En movimiento» (`data` público + `internal` para panel) |
| POST | `/analytics/events` | Contadores agregados (`view_case`, `click_primary_source`); throttle 60/min |
| POST / DELETE | `/cases/{slug}/follow` | Seguir tema (`follower_key` opaco); ver [participacion-seguimiento-aportes.md](../04-datos/participacion-seguimiento-aportes.md) |

Reacciones en ficha incluyen `author_kind` (clasificación de la voz en redes). Valores en [clasificacion-reacciones-redes.md](../04-datos/clasificacion-reacciones-redes.md).
| GET | `/comparisons` | Datos para gráfico según query |
| GET | `/pages/{slug}` | Páginas estáticas (metodología, etc.) |

## Recursos autenticados (admin / agente)

| Método | Ruta | Scope típico |
| --- | --- | --- |
| GET | `/admin/cases` | `content:read` |
| POST | `/admin/cases` | `content:write_draft` |
| PATCH | `/admin/cases/{id}` | `content:write_draft` |
| POST | `/admin/cases/{id}/claims` | `content:write_draft` |
| POST | `/admin/cases/{id}/sources` | `content:write_draft` |
| POST | `/admin/cases/{id}/submit-review` | `content:write_draft` |
| POST | `/admin/cases/{id}/publish` | `content:publish` (humano/revisor) |
| GET | `/admin/review-queue` | `content:review` |

## Modelo `Case` (resumen JSON)

```json
{
  "id": "uuid",
  "slug": "becas-tic-presupuesto-2027",
  "title": "...",
  "summary": "...",
  "areas": ["educacion"],
  "change_types": ["presupuesto"],
  "procedural_status": "en_tramitacion",
  "public_status_label": "en_discusion",
  "evidence_status": "partial",
  "observed_effect": "unknown",
  "content_version": 3,
  "published_at": "2026-10-01T12:00:00Z",
  "reviewed_at": "2026-10-01T11:00:00Z",
  "next_review_at": "2026-11-01",
  "seo": {
    "meta_title": "...",
    "meta_description": "...",
    "og_image_url": "...",
    "robots": "index,follow"
  }
}
```

## Tokens (Sanctum)

| Scope | Uso |
| --- | --- |
| `content:read` | Lectura admin y borradores |
| `content:write_draft` | Crear/editar borradores, afirmaciones, fuentes |
| `content:review` | Cola de revisión, comentarios |
| `content:publish` | Publicar (no asignar a tokens `agente_ia` por defecto) |

## Slug history

`PATCH` que cambia slug crea entrada en `slug_history`; endpoint interno emite 301 desde slug antiguo.

## OpenAPI y agentes

Clientes sin MCP consumen OpenAPI + Bearer token. MCP tools mapean 1:1 a estos endpoints (ver [integracion-mcp.md](integracion-mcp.md)).
