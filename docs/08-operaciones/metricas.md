# Métricas

> Extraído del plan v1.0 §16. No optimizar solo clics o viralidad.

## Glosario: público vs interno

| Tipo | Ejemplos | Dónde se muestra | Regla |
| --- | --- | --- | --- |
| **Editorial** | Estado procedimental, evidencia, efecto, comparación, fechas de revisión | Portada, ficha, badges | Derivado del catálogo publicado; no requiere cookies |
| **Cartera agregada** | “N temas en discusión”, “M con comparación” | Portada | Solo totales; sin ranking moral |
| **Actividad reciente (“En movimiento”)** | Tema con publicación o revisión reciente, piezas en redes añadidas | Portada (lista acotada) | Siempre con **motivos visibles** (ver abajo) |
| **Producto agregado** | Vistas por ficha, búsquedas, clics a fuente | Panel interno primero | Sin perfilado; retención limitada (ver [analytics-eventos.md](analytics-eventos.md)) |
| **Participación** | Seguir tema, aportes, correcciones | Ficha / formularios | No es like/dislike; requiere moderación donde aplique |
| **Calidad editorial** | % fuente primaria, revisiones a tiempo, errores materiales | Solo interno | No usar como “popularidad” |

Lo que **no** publicamos como indicador: votos de acuerdo/desacuerdo, emoji reactions, rankings por indignación o “más polémico”.

## «En movimiento» (definición)

Un tema aparece como **En movimiento** cuando cumple al menos uno de estos criterios verificables (configurables en `config/queperdimos.php`):

1. **Publicación reciente** — `published_at` dentro de los últimos 14 días.
2. **Revisión editorial reciente** — `reviewed_at` dentro de los últimos 30 días.
3. **Eco curado en redes** — al menos 2 piezas en `reactions` asociadas al tema (conteo editorial, no viralidad bruta).
4. **Interés lector (fase 2+)** — vistas agregadas en ventana de 7 días por encima del umbral mínimo, solo si hay volumen suficiente para no identificar lectores.

La UI debe listar los **motivos** (p. ej. “Revisado el 3 oct 2026 · 3 piezas en redes”). El orden es por cantidad de motivos y luego por fecha de actividad más reciente, no por opinión del lector.

## Calidad editorial

- % fichas con fuente primaria enlazada o límite explícito
- % fichas revisadas a tiempo
- Tiempo entre cambio documentado y actualización en sitio
- Errores materiales: cantidad, severidad, tiempo de corrección
- Aportes útiles / aportes recibidos

## Producto (agregados, sin perfilado)

- Visitas, búsquedas, clics a documentos externos
- Quejas por publicidad o lectura móvil

Ver implementación y umbrales en [analytics-eventos.md](analytics-eventos.md).

## Sostenibilidad

- Costo por ficha mantenida
- Horas mensuales de edición
- Ingreso neto / gastos de caja (y por separado vs. trabajo valorado)

## IA

- Tiempo ahorrado por automatización vs. errores introducidos
- Costo mensual por proveedor

## API y snapshots

- `GET /api/v1/stats/portfolio` — agregados de cartera y lista «En movimiento» (subset público).
- Comando `stats:snapshot-portfolio` — guarda un JSON diario en `portfolio_snapshots` para series históricas (panel interno).
