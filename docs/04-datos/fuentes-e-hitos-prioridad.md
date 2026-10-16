# Prioridad: fuentes e hitos

## Por qué primero

Las gráficas de **cronología** y la tendencia por **acto procedimental** dependen de datos estructurados, no solo de texto libre en la ficha.

## `case_sources` (fuentes documentales)

Campos mínimos MVP:

- `case_id`, `title`, `url`, `kind` (ley, presupuesto, informe, comunicado…)
- `published_on` opcional, `locator` (página, artículo)
- `is_primary` boolean — para métrica “% con fuente primaria”

`cases.sources_count` debe derivarse de `count(*)` en producción (hoy puede ser columna manual en demo).

## `case_milestones` (hitos)

- `case_id`, `occurred_on` (fecha)
- `kind` — `procedural_change`, `publication`, `hearing`, `other`
- `label` — texto corto público
- `procedural_status` opcional — si el hito implica cambio de etapa

Alimentan:

- Timeline en ficha
- Componente «En movimiento» cuando hay hito en ventana de 30 días
- Gráficos internos de presión legislativa (temas con hitos recientes por categoría)

## Orden de implementación

1. Migraciones + modelos Eloquent + relaciones en `EditorialCase`
2. Exponer en `GET /v1/cases/{slug}` como `sources[]` y `milestones[]`
3. Seeder demo con 2–3 fuentes e hitos por caso piloto
4. Sustituir `sources_count` manual por `withCount('sources')`
5. UI `#fuentes` y timeline bajo “Cronología” en ficha

## Relación con decisiones futuras

Cuando exista entidad `Decision`, varios `cases` pueden enlazarse al mismo acto; la tendencia institucional se calcula por cluster de `decision_id`, no por votos de usuarios.
