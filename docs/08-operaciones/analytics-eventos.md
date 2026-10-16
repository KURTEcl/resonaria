# Eventos de analytics (agregados)

## Objetivo

Medir demanda de información **sin perfilar** a quien lee. Los datos alimentan el índice «En movimiento» (fase 2) y reportes internos; no se venden ni se cruzan con identidad política.

## Eventos permitidos

| Evento | Cuándo | Payload |
| --- | --- | --- |
| `view_case` | Carga de ficha `/casos/{slug}` | `slug` |
| `click_primary_source` | Clic en enlace a fuente documental (cuando exista en UI) | `slug`, `source_id` opcional |
| `search` | Envío de búsqueda en catálogo | `q` truncado (máx. 120 caracteres) |

No registrar: IP completa en claro, user-agent detallado, fingerprinting, cookies de terceros para publicidad.

## Almacenamiento

Tabla `case_analytics_daily`: por `case_id` y fecha (`views`, `source_clicks`). Los eventos incrementan el contador del día (UTC o zona `America/Santiago` — usar la misma en todo el pipeline).

Búsquedas: tabla opcional `search_analytics_daily` (`query_hash`, `count`) o solo agregado en herramienta externa (Plausible/Umami).

## Ingesta

- `POST /api/v1/analytics/events` — cuerpo JSON `{ "event": "view_case", "slug": "..." }`.
- Rate limit por IP (p. ej. 60/min) vía middleware Laravel.
- Respuesta `204` sin cuerpo.

## Umbrales para UI pública (fase 2)

- Mostrar “Más lecturas esta semana” solo si `views` en 7 días ≥ **50** (config `queperdimos.analytics.public_view_threshold`).
- Nunca mostrar conteo exacto en ficha pública; solo frases cualitativas o sparkline interna.

## Retención

- Mantener agregados diarios **90 días**; archivar o borrar filas antiguas con job mensual.

## Web (Nuxt)

Composable `useAnalytics()` envía `view_case` una vez por sesión de ficha (`sessionStorage`). No bloquea render si la API falla.
