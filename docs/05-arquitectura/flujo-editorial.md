# Flujo editorial

## Estados de publicación

| Estado | Visible en portal | En sitemap |
| --- | --- | --- |
| `draft` | No | No |
| `in_review` | No (preview auth `noindex`) | No |
| `published` | Sí | Sí |
| `archived` | Opcional / solo lectura | No |

## Flujo humano

1. Editor crea borrador (UI o API/MCP).
2. Editor completa afirmaciones, fuentes, comparaciones.
3. `submit-review` → `in_review`.
4. Revisor aprueba o devuelve comentarios.
5. `publish` → `published`; se generan/actualizan SEO y entradas de sitemap.
6. Corrección material → nueva versión; puede requerir re-revisión según política.

## Flujo con agente IA

1. Agente periodista: `upsert_draft_case`, `add_claim`, `link_source` (scope `write_draft`).
2. Agente **no** llama `publish` (token sin scope o tool deshabilitada).
3. Humano revisa en panel mínimo (diff, cola).
4. Tras publicación, agente redes usa `content_version` congelada.

## Invalidación

Al cambiar cifras o conclusiones materiales:

- Incrementar `content_version`
- Marcar paquetes de redes como obsoletos
- Regenerar OG / caché de meta

Ver [../07-ia/orquestacion-agentes.md](../07-ia/orquestacion-agentes.md).
