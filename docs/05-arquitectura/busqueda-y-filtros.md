# Búsqueda y filtros (API)

Alineado con [../02-diseno-ux/rutas-y-filtros.md](../02-diseno-ux/rutas-y-filtros.md).

## Query `GET /api/v1/cases`

| Parámetro | Tipo | Lógica |
| --- | --- | --- |
| `area` | repetible | OR dentro del grupo |
| `estado` | repetible | OR (mapea a `procedural_status` / etiqueta pública) |
| `tipo` | repetible | OR (`change_types`) |
| `q` | string | Búsqueda en título, aliases, resumen (normalizar tildes) |

Entre grupos: **AND**. Sin valor en un grupo: no restringe.

Ejemplo:

```text
/casos?area=educacion&area=mujeres&estado=en-discusion&tipo=presupuesto&q=becas
```

## Validación

- Descartar valores desconocidos con comportamiento predecible (ignorar + log).
- Misma lógica en servidor que en UI Nuxt.

## Búsqueda

- Debounce en cliente; Enter confirma.
- Cancelar requests obsoletos (AbortController).
- No buscar en borradores ni fuentes no autorizadas.

## Índices

- Postgres: GIN/trigram en título y resumen; índices en `slug`, estados y áreas.
- Evaluar motor dedicado post-MVP si el volumen lo exige.
